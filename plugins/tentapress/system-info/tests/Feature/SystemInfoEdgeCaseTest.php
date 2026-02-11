<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use TentaPress\Users\Models\TpUser;

it('gracefully renders system info when settings table is unavailable', function (): void {
    $user = TpUser::query()->create([
        'name' => 'System Admin',
        'email' => 'system-info-edge@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    Schema::dropIfExists('tp_settings');

    $this->actingAs($user)
        ->get('/admin/system-info')
        ->assertOk()
        ->assertViewHas('report', function (array $report): bool {
            return ($report['tentapress']['active_theme'] ?? null) === '—';
        });
});

it('returns a not found json response for missing plugin install attempts', function (): void {
    $user = TpUser::query()->create([
        'name' => 'System Admin',
        'email' => 'system-info-empty@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/plugins/install-attempts/999999')
        ->assertNotFound()
        ->assertJson([
            'message' => 'Install attempt not found.',
        ]);
});
