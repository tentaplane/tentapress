<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Settings\Models\TpSetting;
use TentaPress\Users\Models\TpUser;

it('redirects guests from settings routes to login', function (): void {
    $this->get('/admin/settings')->assertRedirect('/admin/login');
    $this->post('/admin/settings')->assertRedirect('/admin/login');
});

it('allows a super admin to view settings index', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Settings Admin',
        'email' => 'settings-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpPage::query()->create([
        'title' => 'Home',
        'slug' => 'home',
        'status' => 'published',
    ]);

    $this->actingAs($admin)
        ->get('/admin/settings')
        ->assertOk()
        ->assertViewIs('tentapress-settings::index')
        ->assertSee('Settings')
        ->assertSee('Home page');
});

it('persists settings values on successful save', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Settings Admin',
        'email' => 'settings-save@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpPage::query()->create([
        'title' => 'Landing',
        'slug' => 'landing',
        'status' => 'published',
    ]);

    $this->actingAs($admin)
        ->post('/admin/settings', [
            'site_title' => 'TentaPress Test Site',
            'tagline' => 'Fast publishing',
            'home_page_id' => 1,
            'blog_base' => 'updates',
        ])
        ->assertRedirect('/admin/settings')
        ->assertSessionHas('tp_notice_success', 'Settings saved.');

    expect(TpSetting::query()->where('key', 'site.title')->value('value'))->toBe('TentaPress Test Site');
    expect(TpSetting::query()->where('key', 'site.tagline')->value('value'))->toBe('Fast publishing');
    expect(TpSetting::query()->where('key', 'site.home_page_id')->value('value'))->toBe('1');
    expect(TpSetting::query()->where('key', 'site.blog_base')->value('value'))->toBe('updates');
});
