<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable --all --force')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    app()->make(TaxonomySynchronizer::class)->syncRegistered();
});

it('operates standalone with built-in taxonomy persistence', function (): void {
    expect(Schema::hasTable('tp_taxonomies'))->toBeTrue();
    expect(Schema::hasTable('tp_terms'))->toBeTrue();
    expect(Schema::hasTable('tp_term_assignments'))->toBeTrue();

    $records = TpTaxonomy::query()
        ->orderBy('key')
        ->pluck('label', 'key')
        ->all();

    expect($records)->toMatchArray([
        'category' => 'Categories',
        'tag' => 'Tags',
    ]);
});

it('renders taxonomy archive pages without requiring posts plugin enablement', function (): void {
    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $term = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Standalone',
        'slug' => 'standalone',
    ]);

    $this->get('/blog/taxonomy/category/'.$term->slug)
        ->assertOk()
        ->assertSee('Standalone')
        ->assertSee('No posts found for this term.');
});

it('keeps taxonomy admin routes usable with standalone enablement', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Standalone Taxonomy Admin',
        'email' => 'standalone-taxonomy-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/taxonomies')
        ->assertOk()
        ->assertSee('Taxonomies')
        ->assertSee('Categories')
        ->assertSee('Tags');
});
