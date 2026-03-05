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

it('filters posts index by taxonomy term and keeps filter query in pagination links', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Filter Admin',
        'email' => 'taxonomy-filter-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $featured = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Featured',
        'slug' => 'featured',
    ]);

    $general = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'General',
        'slug' => 'general',
    ]);

    for ($index = 1; $index <= 21; $index++) {
        $post = TpPost::query()->create([
            'title' => 'Featured Post '.$index,
            'slug' => 'featured-post-'.$index,
            'status' => 'draft',
        ]);

        TpTermAssignment::query()->create([
            'taxonomy_id' => $taxonomy->id,
            'term_id' => $featured->id,
            'assignable_type' => TpPost::class,
            'assignable_id' => $post->id,
        ]);
    }

    $otherPost = TpPost::query()->create([
        'title' => 'General Post',
        'slug' => 'general-post',
        'status' => 'draft',
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $general->id,
        'assignable_type' => TpPost::class,
        'assignable_id' => $otherPost->id,
    ]);

    $response = $this->actingAs($admin)
        ->get('/admin/posts?taxonomy_term='.$featured->id);

    $response->assertOk()
        ->assertSee('Featured Post 1')
        ->assertDontSee('General Post')
        ->assertSee('taxonomy_term='.$featured->id, false);
});

it('filters pages index by taxonomy term', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Filter Pages Admin',
        'email' => 'taxonomy-filter-pages-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $docs = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Docs',
        'slug' => 'docs',
    ]);

    $legal = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Legal',
        'slug' => 'legal',
    ]);

    $docsPage = TpPage::query()->create([
        'title' => 'Documentation Page',
        'slug' => 'documentation-page',
        'status' => 'draft',
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $docs->id,
        'assignable_type' => TpPage::class,
        'assignable_id' => $docsPage->id,
    ]);

    $legalPage = TpPage::query()->create([
        'title' => 'Legal Page',
        'slug' => 'legal-page',
        'status' => 'draft',
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $legal->id,
        'assignable_type' => TpPage::class,
        'assignable_id' => $legalPage->id,
    ]);

    $this->actingAs($admin)
        ->get('/admin/pages?taxonomy_term='.$docs->id)
        ->assertOk()
        ->assertSee('Documentation Page')
        ->assertDontSee('Legal Page');
});
