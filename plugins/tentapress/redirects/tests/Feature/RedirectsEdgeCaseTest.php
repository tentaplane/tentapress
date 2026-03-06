<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\RedirectsServiceProvider;
use TentaPress\Users\Models\TpUser;

function registerRedirectsProviderForEdgeCases(): void
{
    app()->register(RedirectsServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('denies redirects admin index to non-super-admin users without capability', function (): void {
    registerRedirectsProviderForEdgeCases();

    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'redirects-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/redirects')
        ->assertForbidden();
});

it('blocks self-referential redirect creation', function (): void {
    registerRedirectsProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Loop Admin',
        'email' => 'loop-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/redirects', [
            'source_path' => '/same-path',
            'target_path' => '/same-path',
            'status_code' => 301,
            'is_enabled' => '1',
        ])
        ->assertSessionHasErrors(['source_path']);
});

it('blocks redirect creation for owned static routes', function (): void {
    registerRedirectsProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Conflict Admin',
        'email' => 'conflict-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/redirects', [
            'source_path' => '/admin',
            'target_path' => '/new-admin',
            'status_code' => 301,
            'is_enabled' => '1',
        ])
        ->assertSessionHasErrors(['source_path']);
});

it('does not redirect disabled redirect rules', function (): void {
    registerRedirectsProviderForEdgeCases();

    TpRedirect::query()->create([
        'source_path' => '/disabled-old',
        'target_path' => '/disabled-new',
        'status_code' => 301,
        'is_enabled' => false,
        'origin' => 'manual',
    ]);

    $this->get('/disabled-old')->assertNotFound();
});

it('imports redirects from a mapping report command', function (): void {
    registerRedirectsProviderForEdgeCases();

    $path = storage_path('app/tp-import-reports/test-redirect-mappings.json');
    File::ensureDirectoryExists(dirname($path));

    $report = [
        'source_format' => 'wxr',
        'mappings' => [
            [
                'source_url' => 'https://legacy.example.com/legacy-a',
                'destination_url' => '/new-a',
            ],
            [
                'source_url' => 'https://legacy.example.com/legacy-b',
                'destination_url' => '/new-b',
            ],
        ],
    ];

    File::put($path, json_encode($report, JSON_THROW_ON_ERROR));

    $this->artisan('tp:redirects:import-mappings', [
        'path' => 'tp-import-reports/test-redirect-mappings.json',
    ])->assertSuccessful();

    expect(TpRedirect::query()->fromSource('/legacy-a')->exists())->toBeTrue();
    expect(TpRedirect::query()->fromSource('/legacy-b')->exists())->toBeTrue();
});
