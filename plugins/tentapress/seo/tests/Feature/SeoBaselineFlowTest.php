<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Users\Models\TpUser;

it('redirects guests from seo admin routes to login', function (): void {
    $this->get('/admin/seo')->assertRedirect('/admin/login');
    $this->get('/admin/seo/settings')->assertRedirect('/admin/login');
});

it('allows a super admin to view seo index and persist seo settings', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'SEO Admin',
        'email' => 'seo-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/seo')
        ->assertOk()
        ->assertViewIs('tentapress-seo::index');

    $this->actingAs($admin)
        ->post('/admin/seo/settings', [
            'title_template' => '{{page_title}} - {{site_title}}',
            'default_description' => 'Default SEO description',
            'default_robots' => 'index,follow',
            'canonical_base' => 'https://example.test',
            'blog_title' => 'News',
            'blog_description' => 'Latest updates',
        ])
        ->assertRedirect('/admin/seo/settings')
        ->assertSessionHas('tp_notice_success', 'SEO settings saved.');

    expect(DB::table('tp_settings')->where('key', 'seo.title_template')->value('value'))
        ->toBe('{{page_title}} - {{site_title}}');
    expect(DB::table('tp_settings')->where('key', 'seo.blog_title')->value('value'))
        ->toBe('News');
});

it('allows a super admin to edit page seo metadata and renders seo head tags', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'SEO Admin',
        'email' => 'seo-page-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'site.title'],
        [
            'value' => 'TentaPress',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    $page = TpPage::query()->create([
        'title' => 'SEO Landing',
        'slug' => 'seo-landing',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
    ]);

    $this->actingAs($admin)
        ->get('/admin/seo/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertViewIs('tentapress-seo::page-edit');

    $this->actingAs($admin)
        ->put('/admin/seo/pages/'.$page->id, [
            'title' => 'SEO Landing Title',
            'description' => 'SEO landing description.',
            'canonical_url' => 'https://example.test/seo-landing',
            'robots' => 'index,follow',
            'og_title' => 'SEO Landing OG',
            'og_description' => 'SEO landing OG description.',
            'twitter_title' => 'SEO Landing Twitter',
            'twitter_description' => 'SEO landing Twitter description.',
        ])
        ->assertRedirect('/admin/seo/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'SEO updated.');

    $seo = TpSeoPage::query()->where('page_id', $page->id)->first();

    expect($seo)->not->toBeNull();
    expect($seo?->title)->toBe('SEO Landing Title');
    expect($seo?->canonical_url)->toBe('https://example.test/seo-landing');

    $head = view('tentapress-seo::head', ['page' => $page])->render();

    expect($head)->toContain('<title>SEO Landing Title</title>');
    expect($head)->toContain('name="description" content="SEO landing description."');
    expect($head)->toContain('property="og:title" content="SEO Landing OG"');
    expect($head)->toContain('name="twitter:title" content="SEO Landing Twitter"');
});
