<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Menus\Models\TpMenu;
use TentaPress\Menus\Models\TpMenuLocation;
use TentaPress\Users\Models\TpUser;

it('denies menus index access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Menus Regular User',
        'email' => 'menus-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/menus')
        ->assertForbidden();
});

it('validates menu creation when slug format is invalid', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Menus Admin',
        'email' => 'menus-invalid-slug@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/menus/new')
        ->post('/admin/menus', [
            'name' => 'Invalid Slug Menu',
            'slug' => 'Invalid Slug',
        ])
        ->assertRedirect('/admin/menus/new')
        ->assertSessionHasErrors(['slug']);

    expect(TpMenu::query()->count())->toBe(0);
});

it('validates malformed menu update payloads for location and target fields', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Menus Admin',
        'email' => 'menus-invalid-update@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $menu = TpMenu::query()->create([
        'name' => 'Footer Menu',
        'slug' => 'footer-menu',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->from('/admin/menus/'.$menu->id.'/edit')
        ->put('/admin/menus/'.$menu->id, [
            'name' => 'Footer Menu',
            'slug' => 'footer-menu',
            'items' => [
                [
                    'title' => 'Docs',
                    'url' => '/docs',
                    'target' => '_top',
                    'sort_order' => 1,
                ],
            ],
            'locations' => [
                'primary' => 999999,
            ],
        ])
        ->assertRedirect('/admin/menus/'.$menu->id.'/edit')
        ->assertSessionHasErrors(['items.0.target', 'locations.primary']);
});

it('ignores unknown location keys when syncing menu assignments', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Menus Admin',
        'email' => 'menus-unknown-location@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $menu = TpMenu::query()->create([
        'name' => 'Sidebar Menu',
        'slug' => 'sidebar-menu',
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
            'name' => 'Sidebar Menu',
            'slug' => 'sidebar-menu',
            'locations' => [
                'unknown-slot' => $menu->id,
            ],
        ])
        ->assertRedirect('/admin/menus/'.$menu->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Menu updated.');

    expect(TpMenuLocation::query()->where('location_key', 'unknown-slot')->exists())->toBeFalse();
    expect(TpMenuLocation::query()->count())->toBe(0);
});
