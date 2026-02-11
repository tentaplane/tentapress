<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('redirects guests from the admin dashboard to login', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('allows a super admin to open the admin dashboard', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Admin User',
        'email' => 'dashboard-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertViewIs('tentapress-admin::dashboard');
});
