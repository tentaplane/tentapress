<?php

declare(strict_types=1);

use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    app()->make(TaxonomySynchronizer::class)->syncRegistered();
});

it('redirects guests away from taxonomy admin routes', function (): void {
    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $this->get('/admin/taxonomies')->assertRedirect('/admin/login');
    $this->get('/admin/taxonomies/'.$taxonomy->id.'/terms')->assertRedirect('/admin/login');
});

it('allows a super admin to browse taxonomies and manage terms', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Taxonomy Admin',
        'email' => 'taxonomy-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $this->actingAs($admin)
        ->get('/admin/taxonomies')
        ->assertOk()
        ->assertSee('Taxonomies')
        ->assertSee('Categories');

    $this->actingAs($admin)
        ->post('/admin/taxonomies/'.$taxonomy->id.'/terms', [
            'name' => 'News',
            'slug' => '',
            'description' => 'Latest updates.',
        ])
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms')
        ->assertSessionHas('tp_notice_success', 'Term created.');

    $term = TpTerm::query()->where('taxonomy_id', $taxonomy->id)->where('slug', 'news')->firstOrFail();

    $this->actingAs($admin)
        ->put('/admin/taxonomies/'.$taxonomy->id.'/terms/'.$term->id, [
            'name' => 'Company News',
            'slug' => 'company-news',
            'description' => 'Renamed term.',
        ])
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms/'.$term->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Term updated.');

    $term->refresh();

    expect($term->name)->toBe('Company News');
    expect($term->slug)->toBe('company-news');

    $this->actingAs($admin)
        ->delete('/admin/taxonomies/'.$taxonomy->id.'/terms/'.$term->id)
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms')
        ->assertSessionHas('tp_notice_success', 'Term deleted.');

    expect(TpTerm::query()->whereKey($term->id)->exists())->toBeFalse();
});

it('supports parent term selection for hierarchical taxonomies', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Hierarchical Taxonomy Admin',
        'email' => 'taxonomy-hierarchy@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $parent = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Guides',
        'slug' => 'guides',
    ]);

    $this->actingAs($admin)
        ->post('/admin/taxonomies/'.$taxonomy->id.'/terms', [
            'name' => 'Tutorials',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms')
        ->assertSessionHas('tp_notice_success', 'Term created.');

    $child = TpTerm::query()->where('taxonomy_id', $taxonomy->id)->where('slug', 'tutorials')->firstOrFail();

    expect((int) $child->parent_id)->toBe((int) $parent->id);
});

it('blocks deleting terms that still have children or assignments', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Protected Delete Admin',
        'email' => 'taxonomy-protected-delete@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $parent = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Resources',
        'slug' => 'resources',
    ]);

    TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'parent_id' => $parent->id,
        'name' => 'Case Studies',
        'slug' => 'case-studies',
    ]);

    $this->actingAs($admin)
        ->delete('/admin/taxonomies/'.$taxonomy->id.'/terms/'.$parent->id)
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms')
        ->assertSessionHas('tp_notice_error', 'Delete child terms before removing this term.');

    $leaf = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Announcements',
        'slug' => 'announcements',
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $leaf->id,
        'assignable_type' => 'posts',
        'assignable_id' => 123,
    ]);

    $this->actingAs($admin)
        ->delete('/admin/taxonomies/'.$taxonomy->id.'/terms/'.$leaf->id)
        ->assertRedirect('/admin/taxonomies/'.$taxonomy->id.'/terms')
        ->assertSessionHas('tp_notice_error', 'Remove term assignments before deleting this term.');
});
