<?php

declare(strict_types=1);

use TentaPress\Users\Models\TpUser;

it('forbids authenticated users without the marketing capability', function (): void {
    bootMarketingPlugin();

    $user = TpUser::query()->create([
        'name' => 'Marketing Viewer',
        'email' => 'marketing-viewer@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/marketing')
        ->assertForbidden();
});

it('renders nothing when providers are disabled and no custom scripts are configured', function (): void {
    bootMarketingPlugin();

    expect(trim(view('tentapress-marketing::head')->render()))->toBe('');
    expect(trim(view('tentapress-marketing::body-open')->render()))->toBe('');
    expect(trim(view('tentapress-marketing::body-close')->render()))->toBe('');
});
