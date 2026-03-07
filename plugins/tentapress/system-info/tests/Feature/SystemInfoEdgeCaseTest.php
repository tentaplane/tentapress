<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use TentaPress\SystemInfo\Models\TpPluginInstall;
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
        ->assertViewHas('report', fn (array $report): bool => ($report['tentapress']['active_theme'] ?? null) === '—');
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

it('deletes completed plugin install attempts', function (): void {
    $user = TpUser::query()->create([
        'name' => 'System Admin',
        'email' => 'system-info-delete-attempt@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $attempt = TpPluginInstall::query()->create([
        'package' => 'tentapress/redirects',
        'status' => 'failed',
    ]);

    $this->actingAs($user)
        ->deleteJson('/admin/plugins/install-attempts/' . $attempt->id)
        ->assertOk()
        ->assertJson([
            'message' => 'Install attempt deleted.',
            'deleted_id' => (int) $attempt->id,
        ]);

    expect(TpPluginInstall::query()->find($attempt->id))->toBeNull();
});

it('deletes running plugin install attempts', function (): void {
    $user = TpUser::query()->create([
        'name' => 'System Admin',
        'email' => 'system-info-active-attempt@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $attempt = TpPluginInstall::query()->create([
        'package' => 'tentapress/redirects',
        'status' => 'running',
    ]);

    $this->actingAs($user)
        ->deleteJson('/admin/plugins/install-attempts/' . $attempt->id)
        ->assertOk()
        ->assertJson([
            'message' => 'Install attempt deleted.',
            'deleted_id' => (int) $attempt->id,
        ]);

    expect(TpPluginInstall::query()->find($attempt->id))->toBeNull();
});
