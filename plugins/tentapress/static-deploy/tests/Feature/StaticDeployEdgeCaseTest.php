<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use TentaPress\StaticDeploy\StaticDeployServiceProvider;
use TentaPress\Users\Models\TpUser;

function registerStaticDeployProviderForEdgeCases(): void
{
    app()->register(StaticDeployServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('denies static deploy access to non-super-admin users', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $user = TpUser::query()->create([
        'name' => 'Static Deploy User',
        'email' => 'static-deploy-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/static-deploy')
        ->assertForbidden();
});

it('validates static deploy generate options as booleans', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/static-deploy')
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => 'yes',
            'include_robots' => 'no',
            'compress_html' => 'invalid',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHasErrors(['include_favicon', 'include_robots', 'compress_html']);
});

it('returns not found when no static export archive exists', function (): void {
    registerStaticDeployProviderForEdgeCases();
    File::deleteDirectory(storage_path('app/tp-static'));

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-no-archive@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/static-deploy/download')
        ->assertNotFound();
});

it('generates a build even when the pages table is unavailable', function (): void {
    registerStaticDeployProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Static Deploy Admin',
        'email' => 'static-deploy-missing-pages-table@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    Schema::dropIfExists('tp_pages');

    $this->actingAs($admin)
        ->post('/admin/static-deploy/generate', [
            'include_favicon' => '0',
            'include_robots' => '0',
            'compress_html' => '0',
        ])
        ->assertRedirect('/admin/static-deploy')
        ->assertSessionHas('tp_notice_success');
});
