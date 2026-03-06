<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Models\TpRedirectSuggestion;
use TentaPress\Redirects\RedirectsServiceProvider;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Users\Models\TpUser;

function registerRedirectsProvider(): void
{
    app()->register(RedirectsServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('redirects guests from redirects admin routes to login', function (): void {
    registerRedirectsProvider();

    $this->get('/admin/redirects')->assertRedirect('/admin/login');
    $this->get('/admin/redirects/create')->assertRedirect('/admin/login');
});

it('allows a super admin to create and update redirects', function (): void {
    registerRedirectsProvider();

    $admin = TpUser::query()->create([
        'name' => 'Redirect Admin',
        'email' => 'redirect-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/redirects', [
            'source_path' => '/old-path',
            'target_path' => '/new-path',
            'status_code' => 301,
            'is_enabled' => '1',
        ])
        ->assertSessionHas('tp_notice_success', 'Redirect created.');

    $redirect = TpRedirect::query()->firstOrFail();

    expect((string) $redirect->source_path)->toBe('/old-path');
    expect((string) $redirect->target_path)->toBe('/new-path');
    expect((int) $redirect->status_code)->toBe(301);

    $this->actingAs($admin)
        ->put('/admin/redirects/'.$redirect->id, [
            'source_path' => '/old-path',
            'target_path' => '/new-path-2',
            'status_code' => 302,
            'is_enabled' => '1',
        ])
        ->assertSessionHas('tp_notice_success', 'Redirect updated.');

    expect((string) $redirect->fresh()->target_path)->toBe('/new-path-2');
    expect((int) $redirect->fresh()->status_code)->toBe(302);
});

it('redirects public requests when a matching redirect exists', function (): void {
    registerRedirectsProvider();

    TpRedirect::query()->create([
        'source_path' => '/legacy-url',
        'target_path' => '/new-url',
        'status_code' => 301,
        'is_enabled' => true,
        'origin' => 'manual',
    ]);

    $this->get('/legacy-url')
        ->assertRedirect('/new-url');
});

it('auto-generates a redirect when a page slug changes', function (): void {
    registerRedirectsProvider();

    $admin = TpUser::query()->create([
        'name' => 'Page Admin',
        'email' => 'page-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Original',
        'slug' => 'original-page',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->put('/admin/pages/'.$page->id, [
            'title' => 'Original',
            'slug' => 'updated-page',
            'layout' => '',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => json_encode(['time' => 0, 'blocks' => [], 'version' => '2.28.0'], JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHas('tp_notice_success', 'Page updated.');

    $autoRedirect = TpRedirect::query()->fromSource('/original-page')->first();

    expect($autoRedirect)->not->toBeNull();
    expect((string) $autoRedirect->target_path)->toBe('/updated-page');
    expect((string) $autoRedirect->origin)->toBe('slug_change_page');
});

it('auto-generates a redirect when a post slug changes', function (): void {
    registerRedirectsProvider();

    $admin = TpUser::query()->create([
        'name' => 'Post Admin',
        'email' => 'post-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $post = TpPost::query()->create([
        'title' => 'Original Post',
        'slug' => 'original-post',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->put('/admin/posts/'.$post->id, [
            'title' => 'Original Post',
            'slug' => 'updated-post',
            'layout' => '',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => json_encode(['time' => 0, 'blocks' => [], 'version' => '2.28.0'], JSON_THROW_ON_ERROR),
            'author_id' => '',
            'published_at' => '',
        ])
        ->assertSessionHas('tp_notice_success', 'Post updated.');

    $autoRedirect = TpRedirect::query()->fromSource('/blog/original-post')->first();

    expect($autoRedirect)->not->toBeNull();
    expect((string) $autoRedirect->target_path)->toBe('/blog/updated-post');
    expect((string) $autoRedirect->origin)->toBe('slug_change_post');
});

it('stages slug-change suggestions when auto-apply policy is disabled', function (): void {
    registerRedirectsProvider();

    if (app()->bound(SettingsStore::class)) {
        app()->make(SettingsStore::class)->set('redirects.auto_apply_slug_redirects', '0', true);
    }

    $admin = TpUser::query()->create([
        'name' => 'Suggestion Admin',
        'email' => 'suggestion-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Policy Controlled Page',
        'slug' => 'policy-old-page',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->put('/admin/pages/'.$page->id, [
            'title' => 'Policy Controlled Page',
            'slug' => 'policy-new-page',
            'layout' => '',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'page_doc_json' => json_encode(['time' => 0, 'blocks' => [], 'version' => '2.28.0'], JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHas('tp_notice_success', 'Page updated.');

    expect(TpRedirect::query()->fromSource('/policy-old-page')->exists())->toBeFalse();
    expect(TpRedirectSuggestion::query()->where('source_path', '/policy-old-page')->pending()->exists())->toBeTrue();
});
