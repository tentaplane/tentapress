<?php

declare(strict_types=1);

use TentaPress\Posts\Models\TpPost;
use TentaPress\Users\Models\TpUser;

it('does not expose draft posts on public routes', function (): void {
    TpPost::query()->create([
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'status' => 'draft',
    ]);

    $this->get('/blog/draft-post')->assertNotFound();
    $this->get('/blog')->assertDontSee('Draft Post');
});

it('does not expose posts scheduled in the future', function (): void {
    TpPost::query()->create([
        'title' => 'Scheduled Post',
        'slug' => 'scheduled-post',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/blog/scheduled-post')->assertNotFound();
    $this->get('/blog')->assertDontSee('Scheduled Post');
});

it('returns not found for unknown public post slugs', function (): void {
    $this->get('/blog/non-existent-post')->assertNotFound();
});

it('denies posts admin index access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'posts-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/posts')
        ->assertForbidden();
});
