<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('denies users index access to a non-super-admin without capabilities', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/users')
        ->assertForbidden();
});

it('validates required user fields when creating an admin user', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Super Admin',
        'email' => 'super@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});
