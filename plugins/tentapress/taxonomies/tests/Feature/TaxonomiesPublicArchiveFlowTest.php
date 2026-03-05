<?php

declare(strict_types=1);

use TentaPress\Posts\Models\TpPost;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/posts')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/taxonomies')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    app()->make(TaxonomySynchronizer::class)->syncRegistered();
});

it('renders taxonomy term archives with published posts only and pagination', function (): void {
    $author = TpUser::query()->create([
        'name' => 'Taxonomy Archive Author',
        'email' => 'taxonomy-archive-author@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    $news = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'News',
        'slug' => 'news',
    ]);

    $other = TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Other',
        'slug' => 'other',
    ]);

    for ($index = 1; $index <= 13; $index++) {
        $post = TpPost::query()->create([
            'title' => 'Archive Post '.$index,
            'slug' => 'archive-post-'.$index,
            'status' => 'published',
            'published_at' => now()->subDays($index),
            'author_id' => $author->id,
        ]);

        TpTermAssignment::query()->create([
            'taxonomy_id' => $taxonomy->id,
            'term_id' => $news->id,
            'assignable_type' => TpPost::class,
            'assignable_id' => $post->id,
        ]);
    }

    $draft = TpPost::query()->create([
        'title' => 'Draft News Post',
        'slug' => 'draft-news-post',
        'status' => 'draft',
        'author_id' => $author->id,
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $news->id,
        'assignable_type' => TpPost::class,
        'assignable_id' => $draft->id,
    ]);

    $differentTermPost = TpPost::query()->create([
        'title' => 'Other Term Post',
        'slug' => 'other-term-post',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'author_id' => $author->id,
    ]);

    TpTermAssignment::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'term_id' => $other->id,
        'assignable_type' => TpPost::class,
        'assignable_id' => $differentTermPost->id,
    ]);

    $this->get('/blog/taxonomy/category/news')
        ->assertOk()
        ->assertSee('News')
        ->assertSee('Archive Post 1')
        ->assertDontSee('Draft News Post')
        ->assertDontSee('Other Term Post')
        ->assertSee('page=2', false);

    $this->get('/blog/taxonomy/category/news?page=2')
        ->assertOk()
        ->assertSee('Archive Post 13');
});

it('renders an empty state when a term has no published posts', function (): void {
    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Unassigned',
        'slug' => 'unassigned',
    ]);

    $this->get('/blog/taxonomy/category/unassigned')
        ->assertOk()
        ->assertSee('No posts found for this term.');
});

it('returns not found for invalid taxonomy or term archive requests', function (): void {
    $taxonomy = TpTaxonomy::query()->where('key', 'category')->firstOrFail();
    TpTerm::query()->create([
        'taxonomy_id' => $taxonomy->id,
        'name' => 'Guides',
        'slug' => 'guides',
    ]);

    $this->get('/blog/taxonomy/unknown/guides')->assertNotFound();
    $this->get('/blog/taxonomy/category/missing-term')->assertNotFound();
});
