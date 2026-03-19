<?php

declare(strict_types=1);

use TentaPress\PluginBoilerplate\PluginBoilerplateServiceProvider;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateSettings;
use TentaPress\Users\Models\TpUser;

function registerPluginBoilerplateAutoloader(): void
{
    static $registered = false;

    if ($registered) {
        return;
    }

    spl_autoload_register(static function (string $class): void {
        $prefix = 'TentaPress\\PluginBoilerplate\\';

        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (! is_string($relativeClass) || $relativeClass === '') {
            return;
        }

        $path = base_path('plugins/tentapress/plugin-boilerplate/src/'.str_replace('\\', '/', $relativeClass).'.php');
        if (is_file($path)) {
            require_once $path;
        }
    });

    $registered = true;
}

function registerPluginBoilerplateProvider(): void
{
    registerPluginBoilerplateAutoloader();

    app()->register(PluginBoilerplateServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

it('redirects guests from the boilerplate admin route to login', function (): void {
    registerPluginBoilerplateProvider();

    $this->get('/admin/plugin-boilerplate')->assertRedirect('/admin/login');
});

it('allows a super admin to view the boilerplate settings page', function (): void {
    registerPluginBoilerplateProvider();

    $admin = TpUser::query()->create([
        'name' => 'Plugin Boilerplate Admin',
        'email' => 'plugin-boilerplate-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/plugin-boilerplate')
        ->assertOk()
        ->assertViewIs('tentapress-plugin-boilerplate::index')
        ->assertSee('Plugin Boilerplate')
        ->assertSee('Endpoint prefix');
});

it('persists boilerplate settings through the shared settings store', function (): void {
    registerPluginBoilerplateProvider();

    $admin = TpUser::query()->create([
        'name' => 'Plugin Boilerplate Editor',
        'email' => 'plugin-boilerplate-editor@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/plugin-boilerplate', [
            'plugin_enabled' => '1',
            'endpoint_prefix' => 'starter-plugin',
            'admin_notice' => 'Use explicit dependencies and small services.',
        ])
        ->assertRedirect('/admin/plugin-boilerplate')
        ->assertSessionHas('tp_notice_success', 'Plugin boilerplate settings saved.');

    $settings = app()->make(PluginBoilerplateSettings::class);

    expect($settings->isEnabled())->toBeTrue();
    expect($settings->endpointPrefix())->toBe('starter-plugin');
    expect($settings->adminNotice())->toBe('Use explicit dependencies and small services.');
});

it('registers the boilerplate check command', function (): void {
    registerPluginBoilerplateProvider();

    $this->artisan('tp:plugin-boilerplate:check')
        ->expectsOutputToContain('Enabled')
        ->expectsOutputToContain('Endpoint prefix')
        ->assertSuccessful();
});
