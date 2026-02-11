<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Users\Models\TpUser;

it('redirects guests from themes routes to login', function (): void {
    $this->get('/admin/themes')->assertRedirect('/admin/login');
    $this->post('/admin/themes/activate')->assertRedirect('/admin/login');
});

it('allows a super admin to view themes index', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Themes Admin',
        'email' => 'themes-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/themes')
        ->assertOk()
        ->assertViewIs('tentapress-themes::index');
});

it('activates a theme and persists it in settings', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Themes Admin',
        'email' => 'themes-activate@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    DB::table('tp_themes')->insert([
        'id' => 'tentapress/tailwind',
        'name' => 'Tailwind',
        'version' => '1.0.0',
        'path' => 'themes/tentapress/tailwind',
        'manifest' => json_encode(['id' => 'tentapress/tailwind', 'name' => 'Tailwind'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post('/admin/themes/activate', [
            'theme_id' => 'tentapress/tailwind',
        ])
        ->assertRedirect('/admin/themes')
        ->assertSessionHas('tp_notice_success', 'Theme activated.');

    $rawActiveTheme = DB::table('tp_settings')->where('key', 'active_theme')->value('value');
    expect(json_decode((string) $rawActiveTheme, true))->toBe('tentapress/tailwind');
});
