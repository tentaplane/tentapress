<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/posts')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/pages')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    app()->make(TaxonomySynchronizer::class)->syncRegistered();
});

it('renders taxonomy controls and persists selected terms for post edits', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Post Admin',
        'email' => 'taxonomy-post-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $category = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $tag = TpTaxonomy::query()->where('key', 'tag')->firstOrFail();

    $news = TpTerm::query()->create([
        'taxonomy_id' => $category->id,
        'name' => 'News',
        'slug' => 'news',
    ]);

    $product = TpTerm::query()->create([
        'taxonomy_id' => $category->id,
        'name' => 'Product',
        'slug' => 'product',
    ]);

    $release = TpTerm::query()->create([
        'taxonomy_id' => $tag->id,
        'name' => 'Release',
        'slug' => 'release',
    ]);

    $update = TpTerm::query()->create([
        'taxonomy_id' => $tag->id,
        'name' => 'Update',
        'slug' => 'update',
    ]);

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'Taxonomy Assigned Post',
            'slug' => '',
            'taxonomy_terms' => [
                (string) $category->id => [(string) $news->id],
                (string) $tag->id => [(string) $release->id, (string) $update->id],
            ],
        ])
        ->assertRedirect('/admin/posts/1/edit')
        ->assertSessionHas('tp_notice_success', 'Post created.');

    $post = TpPost::query()->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin/posts/'.$post->id.'/edit')
        ->assertOk()
        ->assertSee('Taxonomies')
        ->assertSee('Categories')
        ->assertSee('Tags');

    $assignedByTaxonomy = TpTermAssignment::query()
        ->where('assignable_type', TpPost::class)
        ->where('assignable_id', $post->id)
        ->orderBy('taxonomy_id')
        ->orderBy('term_id')
        ->get()
        ->groupBy('taxonomy_id')
        ->map(
            static fn ($group): array => $group
                ->pluck('term_id')
                ->map(static fn (mixed $value): int => (int) $value)
                ->all()
        )
        ->all();

    expect($assignedByTaxonomy[(int) $category->id] ?? [])->toBe([$news->id]);
    expect($assignedByTaxonomy[(int) $tag->id] ?? [])->toBe([$release->id, $update->id]);

    $this->actingAs($admin)
        ->put('/admin/posts/'.$post->id, [
            'title' => 'Taxonomy Assigned Post',
            'slug' => (string) $post->slug,
            'taxonomy_terms' => [
                (string) $category->id => [(string) $product->id],
                (string) $tag->id => [(string) $update->id],
            ],
        ])
        ->assertRedirect('/admin/posts/'.$post->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Post updated.');

    $updatedByTaxonomy = TpTermAssignment::query()
        ->where('assignable_type', TpPost::class)
        ->where('assignable_id', $post->id)
        ->orderBy('taxonomy_id')
        ->orderBy('term_id')
        ->get()
        ->groupBy('taxonomy_id')
        ->map(
            static fn ($group): array => $group
                ->pluck('term_id')
                ->map(static fn (mixed $value): int => (int) $value)
                ->all()
        )
        ->all();

    expect($updatedByTaxonomy[(int) $category->id] ?? [])->toBe([$product->id]);
    expect($updatedByTaxonomy[(int) $tag->id] ?? [])->toBe([$update->id]);
});

it('validates invalid taxonomy term combinations during post updates', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Validation Admin',
        'email' => 'taxonomy-validation-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $category = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $tag = TpTaxonomy::query()->where('key', 'tag')->firstOrFail();

    $news = TpTerm::query()->create([
        'taxonomy_id' => $category->id,
        'name' => 'News',
        'slug' => 'news',
    ]);

    $release = TpTerm::query()->create([
        'taxonomy_id' => $tag->id,
        'name' => 'Release',
        'slug' => 'release',
    ]);

    $post = TpPost::query()->create([
        'title' => 'Validation Target',
        'slug' => 'validation-target',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->put('/admin/posts/'.$post->id, [
            'title' => 'Validation Target',
            'slug' => 'validation-target',
            'taxonomy_terms' => [
                (string) $category->id => [(string) $release->id],
            ],
        ])
        ->assertSessionHasErrors('taxonomy_terms.'.$category->id);

    expect(TpTermAssignment::query()->where('assignable_type', TpPost::class)->count())->toBe(0);

    $this->actingAs($admin)
        ->put('/admin/posts/'.$post->id, [
            'title' => 'Validation Target',
            'slug' => 'validation-target',
            'taxonomy_terms' => [
                (string) $category->id => [(string) $news->id],
            ],
        ])
        ->assertRedirect('/admin/posts/'.$post->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Post updated.');

    $assignment = TpTermAssignment::query()
        ->where('assignable_type', TpPost::class)
        ->where('assignable_id', $post->id)
        ->first();

    expect($assignment)->not->toBeNull();
    expect((int) ($assignment?->term_id ?? 0))->toBe((int) $news->id);
});

it('persists selected terms for page editing flows', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Page Admin',
        'email' => 'taxonomy-page-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $category = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $term = TpTerm::query()->create([
        'taxonomy_id' => $category->id,
        'name' => 'Documentation',
        'slug' => 'documentation',
    ]);

    $this->actingAs($admin)
        ->post('/admin/pages', [
            'title' => 'Taxonomy Assigned Page',
            'slug' => '',
            'taxonomy_terms' => [
                (string) $category->id => [(string) $term->id],
            ],
        ])
        ->assertRedirect('/admin/pages/1/edit')
        ->assertSessionHas('tp_notice_success', 'Page created.');

    $page = TpPage::query()->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertSee('Taxonomies')
        ->assertSee('Categories');

    $assignment = TpTermAssignment::query()
        ->where('assignable_type', TpPage::class)
        ->where('assignable_id', $page->id)
        ->first();

    expect($assignment)->not->toBeNull();
    expect((int) ($assignment?->term_id ?? 0))->toBe((int) $term->id);
});
