<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('stores intended url when redirecting a guest from admin dashboard', function (): void {
    $this->get('/admin')
        ->assertRedirect('/admin/login');

    expect(session('url.intended'))->toBe(url('/admin'));
});

it('applies security headers on authenticated admin dashboard responses', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Admin User',
        'email' => 'headers-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});
