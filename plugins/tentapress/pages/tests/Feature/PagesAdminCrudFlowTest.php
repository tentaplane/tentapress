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

it('renders the page edit screens without revisions enabled', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/revisions')->assertSuccessful();

    $admin = TpUser::query()->create([
        'name' => 'Pages Editor',
        'email' => 'pages-editor@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Standalone Page',
        'slug' => 'standalone-page',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertDontSee('Loaded autosave draft')
        ->assertDontSee('Revisions');

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/editor')
        ->assertOk()
        ->assertDontSee('Loaded autosave draft');
});

it('hides taxonomy metabox when taxonomies plugin is disabled', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins disable tentapress/taxonomies --force')->assertSuccessful();

    $admin = TpUser::query()->create([
        'name' => 'Pages Editor Taxonomy Disabled',
        'email' => 'pages-editor-taxonomy-disabled@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Taxonomy Hidden Page',
        'slug' => 'taxonomy-hidden-page',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertDontSee('Taxonomies');
});
