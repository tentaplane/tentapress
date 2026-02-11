<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;

it('denies pages admin access to non-super-admin users', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Pages Regular User',
        'email' => 'pages-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/pages')
        ->assertForbidden();
});

it('validates slug format when creating a page', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-slug-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/pages/new')
        ->post('/admin/pages', [
            'title' => 'Invalid Slug Page',
            'slug' => 'Invalid Slug',
            'layout' => 'default',
        ])
        ->assertRedirect('/admin/pages/new')
        ->assertSessionHasErrors(['slug']);
});

it('validates slug uniqueness when updating a page', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-update-unique@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $firstPage = TpPage::query()->create([
        'title' => 'First Page',
        'slug' => 'first-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $secondPage = TpPage::query()->create([
        'title' => 'Second Page',
        'slug' => 'second-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $this->actingAs($admin)
        ->from('/admin/pages/'.$secondPage->id.'/edit')
        ->put('/admin/pages/'.$secondPage->id, [
            'title' => 'Second Page',
            'slug' => $firstPage->slug,
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
        ])
        ->assertRedirect('/admin/pages/'.$secondPage->id.'/edit')
        ->assertSessionHasErrors(['slug']);
});
