<?php

declare(strict_types=1);

use TentaPress\Posts\Models\TpPost;
use TentaPress\Users\Models\TpUser;

it('redirects guests from posts admin routes to login', function (): void {
    $this->get('/admin/posts')->assertRedirect('/admin/login');
    $this->post('/admin/posts')->assertRedirect('/admin/login');
});

it('allows a super admin to access posts index and create a draft post', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Posts Admin',
        'email' => 'posts-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/posts')
        ->assertOk()
        ->assertViewIs('tentapress-posts::posts.index');

    $this->actingAs($admin)
        ->post('/admin/posts', [
            'title' => 'First Post',
            'slug' => '',
        ])
        ->assertRedirect('/admin/posts/1/edit')
        ->assertSessionHas('tp_notice_success', 'Post created.');

    expect(TpPost::query()->where('title', 'First Post')->value('slug'))->toBe('first-post');
    expect(TpPost::query()->where('title', 'First Post')->value('status'))->toBe('draft');
});

it('renders published posts on public index and post routes', function (): void {
    TpPost::query()->create([
        'title' => 'Published Post',
        'slug' => 'published-post',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('Published Post');

    $this->get('/blog/published-post')
        ->assertOk()
        ->assertSee('Published Post');
});
