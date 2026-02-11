<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Services\SeoManager;
use TentaPress\Users\Models\TpUser;

it('denies seo admin access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'SEO Regular User',
        'email' => 'seo-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/seo')
        ->assertForbidden();
});

it('validates required fields when saving seo settings', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'SEO Admin',
        'email' => 'seo-invalid-settings@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/seo/settings')
        ->post('/admin/seo/settings', [
            'title_template' => '',
            'default_robots' => '',
        ])
        ->assertRedirect('/admin/seo/settings')
        ->assertSessionHasErrors(['title_template', 'default_robots']);
});

it('removes page seo rows when all seo fields are emptied', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'SEO Admin',
        'email' => 'seo-empty-row@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Edge SEO Page',
        'slug' => 'edge-seo-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
    ]);

    TpSeoPage::query()->create([
        'page_id' => $page->id,
        'title' => 'Custom SEO Title',
        'description' => 'Custom SEO description.',
        'canonical_url' => 'https://example.test/edge-seo-page',
        'robots' => 'index,follow',
    ]);

    $this->actingAs($admin)
        ->put('/admin/seo/pages/'.$page->id, [
            'title' => '',
            'description' => '',
            'canonical_url' => '',
            'robots' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'twitter_title' => '',
            'twitter_description' => '',
            'twitter_image' => '',
        ])
        ->assertRedirect('/admin/seo/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'SEO updated.');

    expect(TpSeoPage::query()->where('page_id', $page->id)->exists())->toBeFalse();
});

it('falls back to site settings when no custom seo row exists', function (): void {
    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'site.title'],
        ['value' => 'TentaPress Site', 'autoload' => true, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'seo.title_template'],
        ['value' => '{{page_title}} · {{site_title}}', 'autoload' => true, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'seo.default_description'],
        ['value' => 'Default SEO description.', 'autoload' => true, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'seo.default_robots'],
        ['value' => 'index,follow', 'autoload' => true, 'created_at' => now(), 'updated_at' => now()]
    );

    $page = TpPage::query()->create([
        'title' => 'Fallback SEO Page',
        'slug' => 'fallback-seo-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
    ]);

    $meta = app(SeoManager::class)->forPage($page);

    expect($meta['title'] ?? null)->toBe('Fallback SEO Page · TentaPress Site');
    expect($meta['description'] ?? null)->toBe('Default SEO description.');
    expect($meta['robots'] ?? null)->toBe('index,follow');
});
