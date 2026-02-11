<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuItem;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Users\Models\TpUser;

it('redirects guests from menu admin routes to login', function (): void {
    $this->get('/admin/menus')->assertRedirect('/admin/login');
    $this->post('/admin/menus')->assertRedirect('/admin/login');
});

it('allows a super admin to list menus and create a menu', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Menus Admin',
        'email' => 'menus-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/menus')
        ->assertOk()
        ->assertViewIs('tentapress-menus::menus.index');

    $this->actingAs($admin)
        ->post('/admin/menus', [
            'name' => 'Header Navigation',
            'slug' => '',
        ])
        ->assertRedirect('/admin/menus/1/edit')
        ->assertSessionHas('tp_notice_success', 'Menu created.');

    $menu = TpMenu::query()->find(1);

    expect($menu)->not->toBeNull();
    expect($menu?->name)->toBe('Header Navigation');
    expect($menu?->slug)->toBe('header-navigation');
});

it('allows a super admin to update menu items and assign locations, then delete the menu', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Menus Admin',
        'email' => 'menus-admin-update@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $menu = TpMenu::query()->create([
        'name' => 'Main Menu',
        'slug' => 'main-menu',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    DB::table('tp_themes')->insert([
        'id' => 'tentapress/tailwind',
        'name' => 'Tailwind',
        'version' => '1.0.0',
        'path' => 'themes/tentapress/tailwind',
        'manifest' => json_encode([
            'id' => 'tentapress/tailwind',
            'name' => 'Tailwind',
            'menu_locations' => [
                'primary' => 'Primary Navigation',
                'footer' => 'Footer Navigation',
            ],
        ], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tp_settings')->updateOrInsert(
        ['key' => 'active_theme'],
        [
            'value' => json_encode('tentapress/tailwind', JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    $themeCachePath = base_path('bootstrap/cache/tp_theme.php');
    if (is_file($themeCachePath)) {
        @unlink($themeCachePath);
    }

    $this->actingAs($admin)
        ->put('/admin/menus/'.$menu->id, [
            'name' => 'Primary Menu',
            'slug' => 'primary-menu',
            'items' => [
                [
                    'title' => 'Home',
                    'url' => '/',
                    'target' => '_self',
                    'parent_id' => null,
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Blog',
                    'url' => '/blog',
                    'target' => '',
                    'parent_id' => null,
                    'sort_order' => 2,
                ],
            ],
            'locations' => [
                'primary' => $menu->id,
                'footer' => '',
            ],
        ])
        ->assertRedirect('/admin/menus/'.$menu->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Menu updated.');

    $menu->refresh();

    expect($menu->name)->toBe('Primary Menu');
    expect($menu->slug)->toBe('primary-menu');

    expect(TpMenuItem::query()->where('menu_id', $menu->id)->count())->toBe(2);
    expect(
        TpMenuLocation::query()
            ->where('location_key', 'primary')
            ->where('menu_id', $menu->id)
            ->exists()
    )->toBeTrue();

    $this->actingAs($admin)
        ->delete('/admin/menus/'.$menu->id)
        ->assertRedirect('/admin/menus')
        ->assertSessionHas('tp_notice_success', 'Menu deleted.');

    expect(TpMenu::query()->find($menu->id))->toBeNull();
    expect(TpMenuItem::query()->where('menu_id', $menu->id)->count())->toBe(0);
    expect(TpMenuLocation::query()->where('menu_id', $menu->id)->count())->toBe(0);
});
