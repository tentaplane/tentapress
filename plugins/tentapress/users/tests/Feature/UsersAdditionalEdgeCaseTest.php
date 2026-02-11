<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use TentaPress\Users\Models\TpUser;

it('validates required login fields for admin login', function (): void {
    $this->from('/admin/login')
        ->post('/admin/login', [
            'email' => '',
            'password' => '',
        ])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors(['email', 'password']);
});

it('validates unique email when creating a user', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Users Admin',
        'email' => 'users-edge-admin@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => true,
    ]);

    TpUser::query()->create([
        'name' => 'Existing User',
        'email' => 'existing-user@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => false,
    ]);

    $this->actingAs($admin)
        ->from('/admin/users/new')
        ->post('/admin/users', [
            'name' => 'Duplicate Email User',
            'email' => 'existing-user@example.test',
            'password' => 'new-user-password',
            'is_super_admin' => '0',
        ])
        ->assertRedirect('/admin/users/new')
        ->assertSessionHasErrors(['email']);
});

it('denies roles management routes to non-super-admin users', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'roles-regular@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/roles')
        ->assertForbidden();
});
