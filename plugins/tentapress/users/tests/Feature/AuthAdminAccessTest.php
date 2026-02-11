<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('shows the admin login page to guests', function (): void {
    $this->get('/admin/login')->assertOk();
});

it('allows a super admin to access the users index', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertOk();
});
