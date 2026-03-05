<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\System\Support\Paths;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;
use TentaPress\Users\Models\TpUser;

function seedThemeWithoutRequestedLocation(): void
{
    DB::table('tp_themes')->updateOrInsert(
        ['id' => 'tentapress/tailwind'],
        [
            'name' => 'Tailwind',
            'version' => '1.0.0',
            'path' => 'themes/tentapress/tailwind',
            'manifest' => json_encode([
                'id' => 'tentapress/tailwind',
                'name' => 'Tailwind',
                'menu_locations' => [
                    'footer' => 'Footer Navigation',
                ],
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'active_theme'],
        [
            'value' => json_encode('tentapress/tailwind', JSON_THROW_ON_ERROR),
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    $themeCachePath = Paths::themeCachePath();
    if (is_file($themeCachePath)) {
        @unlink($themeCachePath);
    }
}

if (! function_exists('ensureTaxonomiesApiFixturesAvailable')) {
    function ensureTaxonomiesApiFixturesAvailable(): void
    {
        if (
            Schema::hasTable('tp_taxonomies')
            && Schema::hasTable('tp_terms')
            && Schema::hasTable('tp_term_assignments')
            && TpTaxonomy::query()->where('key', 'category')->exists()
        ) {
            return;
        }

        test()->artisan('tp:plugins sync')->assertSuccessful();
        test()->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
        test()->refreshApplication();
        test()->artisan('migrate', ['--force' => true])->assertSuccessful();
        app()->make(TaxonomySynchronizer::class)->syncRegistered();
    }
}

it('hides unpublished pages from index and show responses', function (): void {
    TpPage::query()->create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $index = $this->getJson('/api/v1/pages');
    $index->assertOk()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonCount(0, 'data');

    $this->getJson('/api/v1/pages/draft-page')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found')
        ->assertJsonPath('error.message', 'Page not found');
});

it('hides draft and future posts from index and show responses', function (): void {
    $author = TpUser::query()->create([
        'name' => 'Future Author',
        'email' => 'future-author@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpPost::query()->create([
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'status' => 'draft',
        'layout' => 'post',
        'blocks' => [],
        'author_id' => $author->id,
    ]);

    TpPost::query()->create([
        'title' => 'Scheduled Post',
        'slug' => 'scheduled-post',
        'status' => 'published',
        'layout' => 'post',
        'blocks' => [],
        'published_at' => now()->addDay(),
        'author_id' => $author->id,
    ]);

    $index = $this->getJson('/api/v1/posts');
    $index->assertOk()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonCount(0, 'data');

    $this->getJson('/api/v1/posts/draft-post')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found');

    $this->getJson('/api/v1/posts/scheduled-post')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found');
});

it('supports index filters and per_page clamping for pages and posts', function (): void {
    ensureTaxonomiesApiFixturesAvailable();

    $authorA = TpUser::query()->create([
        'name' => 'Filter Author A',
        'email' => 'filter-author-a@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $authorB = TpUser::query()->create([
        'name' => 'Filter Author B',
        'email' => 'filter-author-b@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpPage::query()->create([
        'title' => 'Landing',
        'slug' => 'landing',
        'status' => 'published',
        'layout' => 'landing',
        'blocks' => [],
    ]);

    TpPage::query()->create([
        'title' => 'Contact',
        'slug' => 'contact',
        'status' => 'published',
        'layout' => 'default',
        'blocks' => [],
    ]);

    resolve(SettingsStore::class)->set('site.blog_base', 'Invalid Base');

    TpPost::query()->create([
        'title' => 'Alpha Story',
        'slug' => 'alpha-story',
        'status' => 'published',
        'layout' => 'post',
        'blocks' => [],
        'published_at' => now()->subMinutes(2),
        'author_id' => $authorA->id,
    ]);

    TpPost::query()->create([
        'title' => 'Beta Story',
        'slug' => 'beta-story',
        'status' => 'published',
        'layout' => 'post',
        'blocks' => [],
        'published_at' => now()->subMinute(),
        'author_id' => $authorB->id,
    ]);

    $pagesByLayout = $this->getJson('/api/v1/pages?layout=landing&per_page=999');
    $pagesByLayout->assertOk()
        ->assertJsonPath('meta.per_page', 100)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'landing');

    $pageBySlug = $this->getJson('/api/v1/pages?slug=contact&per_page=0');
    $pageBySlug->assertOk()
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'contact');

    $postsByAuthor = $this->getJson('/api/v1/posts?author='.$authorA->id.'&per_page=0');
    $postsByAuthor->assertOk()
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'alpha-story');

    $postsByQuery = $this->getJson('/api/v1/posts?q=beta&per_page=500');
    $postsByQuery->assertOk()
        ->assertJsonPath('meta.per_page', 100)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'beta-story')
        ->assertJsonPath('data.0.permalink', '/blog/beta-story');

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $term = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Product',
        'slug' => 'product',
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $term->id,
        'assignable_type' => TpPost::class,
        'assignable_id' => TpPost::query()->where('slug', 'alpha-story')->value('id'),
    ]);

    $postsByTaxonomy = $this->getJson('/api/v1/posts?taxonomy=category&term=product');
    $postsByTaxonomy->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.filters.taxonomy', 'category')
        ->assertJsonPath('meta.filters.term', 'product')
        ->assertJsonPath('data.0.slug', 'alpha-story');
});

it('returns consistent not found envelope for unknown menu location and media id', function (): void {
    seedThemeWithoutRequestedLocation();

    $this->getJson('/api/v1/menus/primary')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found')
        ->assertJsonPath('error.message', 'Menu location not found');

    $this->getJson('/api/v1/media/9999')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found')
        ->assertJsonPath('error.message', 'Media not found');
});

it('returns taxonomy not found for unknown taxonomy term endpoints', function (): void {
    $this->getJson('/api/v1/taxonomies/unknown/terms')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found')
        ->assertJsonPath('error.message', 'Taxonomy not found');
});

it('supports rendering fallbacks for blocks and page-editor payloads', function (): void {
    app()->instance('tp.blocks.render', static fn (array $blocks): string => 'blocks:'.count($blocks));
    app()->instance('tp.page_editor.render', static fn (array $content): string => 'page-editor:'.($content['doc'] ?? ''));

    TpPage::query()->create([
        'title' => 'Blocks Rendered',
        'slug' => 'blocks-rendered',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            ['type' => 'blocks/content', 'props' => ['content' => 'One']],
            ['type' => 'blocks/content', 'props' => ['content' => 'Two']],
        ],
    ]);

    $blocksPage = $this->getJson('/api/v1/pages/blocks-rendered');
    $blocksPage->assertOk()
        ->assertJsonPath('data.content_html', 'blocks:2')
        ->assertJsonPath('data.content_raw.editor_driver', 'blocks');

    $builder = resolve(ContentPayloadBuilder::class);
    $virtualPage = new TpPage([
        'editor_driver' => 'builder',
        'blocks' => [],
        'content' => ['doc' => 'ok'],
    ]);

    $payload = $builder->forPage($virtualPage);

    expect($payload['content_html'])->toBe('page-editor:ok');
    expect($payload['content_raw']['editor_driver'] ?? null)->toBe('builder');
});
