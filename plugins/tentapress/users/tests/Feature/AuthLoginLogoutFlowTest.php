<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use TentaPress\Users\Models\TpUser;

it('rejects invalid admin login credentials', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Login User',
        'email' => 'login-invalid@example.test',
        'password' => Hash::make('correct-password'),
        'is_super_admin' => true,
    ]);

    $response = $this->from('/admin/login')
        ->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors(['email']);

    expect(auth()->check())->toBeFalse();
});

it('logs in with valid credentials and redirects to intended admin route', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Login User',
        'email' => 'login-valid@example.test',
        'password' => Hash::make('correct-password'),
        'is_super_admin' => true,
    ]);

    $this->get('/admin/users')->assertRedirect('/admin/login');

    $this->post('/admin/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ])->assertRedirect('/admin/users');

    expect(auth()->check())->toBeTrue();
    expect((int) auth()->id())->toBe((int) $user->id);
});

it('logs out an authenticated admin user', function (): void {
    $createdUser = TpUser::query()->create([
        'name' => 'Logout User',
        'email' => 'logout@example.test',
        'password' => Hash::make('secret-password'),
        'is_super_admin' => true,
    ]);
    $user = TpUser::query()->findOrFail($createdUser->id);

    $this->actingAs($user)
        ->post('/admin/logout')
        ->assertRedirect('/admin/login')
        ->assertSessionHas('tp_notice_success', 'You have been logged out.');

    expect(auth()->check())->toBeFalse();
});
