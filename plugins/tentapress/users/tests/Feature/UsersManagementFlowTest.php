<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use TentaPress\Users\Models\TpUser;

it('allows super admin to create and edit a user account', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Super Admin',
        'email' => 'users-create-admin@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Created User',
            'email' => 'created-user@example.test',
            'password' => 'new-user-password',
            'is_super_admin' => '0',
            'roles' => [],
        ])
        ->assertRedirect();

    $created = TpUser::query()->where('email', 'created-user@example.test')->first();

    expect($created)->not->toBeNull();
    expect(Hash::check('new-user-password', (string) $created->password))->toBeTrue();

    $this->actingAs($admin)
        ->put('/admin/users/'.$created->id, [
            'name' => 'Updated User Name',
            'email' => 'created-user@example.test',
            'password' => '',
            'is_super_admin' => '0',
            'roles' => [],
        ])
        ->assertRedirect('/admin/users/'.$created->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'User updated.');

    $created->refresh();

    expect($created->name)->toBe('Updated User Name');
});

it('prevents removing your own admin access without keeping a role', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Self Edit Admin',
        'email' => 'self-edit-admin@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/users/'.$admin->id.'/edit')
        ->put('/admin/users/'.$admin->id, [
            'name' => 'Self Edit Admin',
            'email' => 'self-edit-admin@example.test',
            'password' => '',
            'is_super_admin' => '0',
            'roles' => [],
        ])
        ->assertRedirect('/admin/users/'.$admin->id.'/edit')
        ->assertSessionHasErrors(['roles']);
});

it('prevents deleting your own account', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Self Delete Admin',
        'email' => 'self-delete-admin@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/users')
        ->delete('/admin/users/'.$admin->id)
        ->assertRedirect('/admin/users')
        ->assertSessionHasErrors(['user']);

    expect(TpUser::query()->whereKey($admin->id)->exists())->toBeTrue();
});
