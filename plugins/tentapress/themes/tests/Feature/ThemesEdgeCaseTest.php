<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\Users\Models\TpUser;

it('denies themes index access to non-super-admin users without capabilities', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'themes-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/themes')
        ->assertForbidden();
});

it('returns server error when activating a theme that does not exist', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Themes Admin',
        'email' => 'themes-missing@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/themes/activate', [
            'theme_id' => 'missing/vendor-theme',
        ])
        ->assertStatus(500);
});

it('shows an empty fallback message when no themes are available', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Themes Admin',
        'email' => 'themes-empty@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    DB::table('tp_themes')->delete();

    $this->actingAs($admin)
        ->get('/admin/themes')
        ->assertOk()
        ->assertSee('No themes found');
});
