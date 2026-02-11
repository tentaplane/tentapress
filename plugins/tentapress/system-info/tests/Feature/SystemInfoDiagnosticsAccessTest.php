<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('redirects guests from system info routes to login', function (): void {
    $this->get('/admin/system-info')->assertRedirect('/admin/login');
    $this->get('/admin/system-info/diagnostics')->assertRedirect('/admin/login');
});

it('allows a super admin to view system info and download diagnostics', function (): void {
    $user = TpUser::query()->create([
        'name' => 'System Admin',
        'email' => 'system-info-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/system-info')
        ->assertOk()
        ->assertViewIs('tentapress-system-info::index')
        ->assertViewHas('report');

    $diagnostics = $this->actingAs($user)
        ->get('/admin/system-info/diagnostics')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json; charset=utf-8');

    $contentDisposition = (string) $diagnostics->headers->get('Content-Disposition');
    expect($contentDisposition)->toStartWith('attachment; filename="tentapress-diagnostics-');

    $payload = json_decode((string) $diagnostics->getContent(), true);
    expect($payload)->toBeArray()
        ->toHaveKeys(['generated_at', 'php', 'laravel', 'runtime', 'storage', 'paths', 'tentapress', 'filesystem', 'config_summary']);
});
