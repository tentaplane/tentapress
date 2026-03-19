<?php

declare(strict_types=1);

use TentaPress\Boilerplate\BoilerplateServiceProvider;
use TentaPress\Boilerplate\Services\BoilerplateSettings;
use TentaPress\Users\Models\TpUser;

function registerBoilerplateAutoloader(): void
{
    static $registered = false;

    if ($registered) {
        return;
    }

    spl_autoload_register(static function (string $class): void {
        $prefix = 'TentaPress\\Boilerplate\\';

        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (! is_string($relativeClass) || $relativeClass === '') {
            return;
        }

        $path = base_path('plugins/tentapress/boilerplate/src/'.str_replace('\\', '/', $relativeClass).'.php');
        if (is_file($path)) {
            require_once $path;
        }
    });

    $registered = true;
}

function registerBoilerplateProvider(): void
{
    registerBoilerplateAutoloader();

    app()->register(BoilerplateServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('redirects guests from the boilerplate admin route to login', function (): void {
    registerBoilerplateProvider();

    $this->get('/admin/boilerplate')->assertRedirect('/admin/login');
});

it('allows a super admin to view the boilerplate settings page', function (): void {
    registerBoilerplateProvider();

    $admin = TpUser::query()->create([
        'name' => 'Boilerplate Admin',
        'email' => 'boilerplate-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/boilerplate')
        ->assertOk()
        ->assertViewIs('tentapress-boilerplate::index')
        ->assertSee('Boilerplate')
        ->assertSee('Endpoint prefix');
});

it('persists boilerplate settings through the shared settings store', function (): void {
    registerBoilerplateProvider();

    $admin = TpUser::query()->create([
        'name' => 'Boilerplate Editor',
        'email' => 'boilerplate-editor@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/boilerplate', [
            'plugin_enabled' => '1',
            'endpoint_prefix' => 'starter-plugin',
            'admin_notice' => 'Use explicit dependencies and small services.',
        ])
        ->assertRedirect('/admin/boilerplate')
        ->assertSessionHas('tp_notice_success', 'Boilerplate settings saved.');

    $settings = app()->make(BoilerplateSettings::class);

    expect($settings->isEnabled())->toBeTrue();
    expect($settings->endpointPrefix())->toBe('starter-plugin');
    expect($settings->adminNotice())->toBe('Use explicit dependencies and small services.');
});

it('registers the boilerplate check command', function (): void {
    registerBoilerplateProvider();

    $this->artisan('tp:boilerplate:check')
        ->expectsOutputToContain('Enabled')
        ->expectsOutputToContain('Endpoint prefix')
        ->assertSuccessful();
});
