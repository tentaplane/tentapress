<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Models\TpSeoPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\System\Support\Paths;
use TentaPress\Users\Models\TpUser;

function seedThemeMenuLocation(string $locationKey = 'primary'): void
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
                    $locationKey => 'Primary Navigation',
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

it('returns site settings payload with normalized blog base', function (): void {
    $settings = resolve(SettingsStore::class);
    $settings->set('site.title', 'TentaPress Headless');
    $settings->set('site.tagline', 'API first');
    $settings->set('site.home_page_id', '12');
    $settings->set('site.blog_base', 'news-feed');

    $response = $this->getJson('/api/v1/site');

    $response->assertOk()
        ->assertJsonPath('data.site.title', 'TentaPress Headless')
        ->assertJsonPath('data.site.tagline', 'API first')
        ->assertJsonPath('data.site.home_page_id', 12)
        ->assertJsonPath('data.site.blog_base', 'news-feed');

    expect((string) $response->json('data.generated_at'))->not->toBe('');
});

it('returns published pages index and show payloads with seo data', function (): void {
    $page = TpPage::query()->create([
        'title' => 'About',
        'slug' => 'about',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'About body'],
            ],
        ],
        'published_at' => now()->subMinute(),
    ]);

    TpSeoPage::query()->create([
        'page_id' => $page->id,
        'title' => 'About SEO',
        'description' => 'About description',
        'canonical_url' => 'https://example.test/about',
    ]);

    $index = $this->getJson('/api/v1/pages');
    $index->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 12)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'about')
        ->assertJsonPath('data.0.seo.title', 'About SEO');

    $show = $this->getJson('/api/v1/pages/about');
    $show->assertOk()
        ->assertJsonPath('data.type', 'page')
        ->assertJsonPath('data.slug', 'about')
        ->assertJsonPath('data.permalink', '/about')
        ->assertJsonPath('data.seo.description', 'About description');
});

it('returns published posts index and show payloads including author and permalink', function (): void {
    resolve(SettingsStore::class)->set('site.blog_base', 'news');

    $author = TpUser::query()->create([
        'name' => 'Headless Author',
        'email' => 'headless-author@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $post = TpPost::query()->create([
        'title' => 'Hello API',
        'slug' => 'hello-api',
        'status' => 'published',
        'layout' => 'post',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'published_at' => now()->subMinute(),
        'author_id' => $author->id,
    ]);

    TpSeoPost::query()->create([
        'post_id' => $post->id,
        'title' => 'Post SEO',
        'description' => 'Post description',
    ]);

    $index = $this->getJson('/api/v1/posts');
    $index->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.slug', 'hello-api')
        ->assertJsonPath('data.0.author.name', 'Headless Author')
        ->assertJsonPath('data.0.permalink', '/news/hello-api');

    $show = $this->getJson('/api/v1/posts/hello-api');
    $show->assertOk()
        ->assertJsonPath('data.slug', 'hello-api')
        ->assertJsonPath('data.author.id', $author->id)
        ->assertJsonPath('data.seo.title', 'Post SEO')
        ->assertJsonPath('data.permalink', '/news/hello-api');
});

it('returns menu and media payloads for valid identifiers', function (): void {
    seedThemeMenuLocation('primary');

    $menu = TpMenu::query()->create([
        'name' => 'Main Menu',
        'slug' => 'main-menu',
    ]);

    TpMenuLocation::query()->create([
        'location_key' => 'primary',
        'menu_id' => $menu->id,
    ]);

    TpMenuItem::query()->create([
        'menu_id' => $menu->id,
        'parent_id' => null,
        'title' => 'Home',
        'url' => '/',
        'target' => '_self',
        'sort_order' => 1,
    ]);

    $menuResponse = $this->getJson('/api/v1/menus/primary');
    $menuResponse->assertOk()
        ->assertJsonPath('data.location', 'primary')
        ->assertJsonPath('data.menu.name', 'Main Menu')
        ->assertJsonPath('data.menu.items.0.title', 'Home');

    $media = TpMedia::query()->create([
        'title' => 'Hero Image',
        'alt_text' => 'Hero alt',
        'caption' => 'Hero caption',
        'disk' => 'public',
        'path' => 'media/hero.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 12345,
        'width' => 1920,
        'height' => 1080,
    ]);

    $mediaResponse = $this->getJson('/api/v1/media/'.$media->id);
    $mediaResponse->assertOk()
        ->assertJsonPath('data.id', $media->id)
        ->assertJsonPath('data.mime_type', 'image/jpeg')
        ->assertJsonPath('data.width', 1920)
        ->assertJsonPath('data.height', 1080);

    expect((string) $mediaResponse->json('data.url'))->not->toBe('');
});
