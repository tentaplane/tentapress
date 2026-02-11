<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;

it('redirects guests from pages admin routes to login', function (): void {
    $this->get('/admin/pages')->assertRedirect('/admin/login');
    $this->get('/admin/pages/new')->assertRedirect('/admin/login');
    $this->post('/admin/pages')->assertRedirect('/admin/login');
});

it('allows a super admin to create publish unpublish and delete a page', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-admin-flow@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $createResponse = $this->actingAs($admin)
        ->post('/admin/pages', [
            'title' => 'Landing Page',
            'slug' => '',
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
        ]);

    $createResponse
        ->assertRedirect()
        ->assertSessionHas('tp_notice_success', 'Page created.');

    $page = TpPage::query()->where('title', 'Landing Page')->first();

    expect($page)->not->toBeNull();
    expect($page->status)->toBe('draft');

    $this->actingAs($admin)
        ->post('/admin/pages/'.$page->id.'/publish')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Page published.');

    $page->refresh();
    expect($page->status)->toBe('published');

    $this->actingAs($admin)
        ->post('/admin/pages/'.$page->id.'/unpublish')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Page set to draft.');

    $page->refresh();
    expect($page->status)->toBe('draft');

    $this->actingAs($admin)
        ->delete('/admin/pages/'.$page->id)
        ->assertRedirect('/admin/pages')
        ->assertSessionHas('tp_notice_success', 'Page deleted.');

    expect(TpPage::query()->whereKey($page->id)->exists())->toBeFalse();
});
