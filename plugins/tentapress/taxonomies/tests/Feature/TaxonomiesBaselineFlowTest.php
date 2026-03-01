<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;
use TentaPress\Taxonomies\Support\TaxonomyDefinition;
use TentaPress\Taxonomies\Support\TaxonomyRegistry;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    app()->make(TaxonomySynchronizer::class)->syncRegistered();
});

it('registers and persists the built-in category and tag taxonomies', function (): void {
    /** @var TaxonomyRegistry $registry */
    $registry = app()->make(TaxonomyRegistry::class);

    expect($registry->has('category'))->toBeTrue();
    expect($registry->has('tag'))->toBeTrue();

    $records = TpTaxonomy::query()
        ->orderBy('key')
        ->pluck('label', 'key')
        ->all();

    expect($records)->toMatchArray([
        'category' => 'Categories',
        'tag' => 'Tags',
    ]);
});

it('creates the taxonomy persistence tables and relationships', function (): void {
    expect(Schema::hasTable('tp_taxonomies'))->toBeTrue();
    expect(Schema::hasTable('tp_terms'))->toBeTrue();
    expect(Schema::hasTable('tp_term_assignments'))->toBeTrue();

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();

    $parent = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'News',
        'slug' => 'news',
    ]);

    $child = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'parent_id' => $parent->id,
        'name' => 'Company News',
        'slug' => 'company-news',
    ]);

    $assignment = TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $child->id,
        'assignable_type' => 'posts',
        'assignable_id' => 1,
    ]);

    expect($parent->children()->pluck('id')->all())->toBe([$child->id]);
    expect($child->parent?->is($parent))->toBeTrue();
    expect($assignment->term->is($child))->toBeTrue();
    expect($assignment->taxonomy->is($taxonomy))->toBeTrue();
});

it('supports custom taxonomy registration and syncs it into persistence', function (): void {
    /** @var TaxonomyRegistry $registry */
    $registry = app()->make(TaxonomyRegistry::class);

    $registry->register(new TaxonomyDefinition(
        key: 'topic',
        label: 'Topics',
        singularLabel: 'Topic',
        description: 'Custom test taxonomy.',
        config: [
            'supports_multiple_terms' => true,
        ],
    ));

    app()->make(TaxonomySynchronizer::class)->syncRegistered();

    $topic = TpTaxonomy::query()->where('key', 'topic')->first();

    expect($topic)->not->toBeNull();
    expect($topic?->label)->toBe('Topics');
    expect($topic?->config)->toMatchArray([
        'supports_multiple_terms' => true,
    ]);
});

it('rejects duplicate taxonomy registration keys', function (): void {
    /** @var TaxonomyRegistry $registry */
    $registry = app()->make(TaxonomyRegistry::class);

    $registry->register(new TaxonomyDefinition(
        key: 'audience',
        label: 'Audiences',
        singularLabel: 'Audience',
    ));

    $registry->register(new TaxonomyDefinition(
        key: 'audience',
        label: 'Audiences',
        singularLabel: 'Audience',
    ));
})->throws(InvalidArgumentException::class, 'Taxonomy [audience] is already registered.');
