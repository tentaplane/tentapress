<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use TentaPress\System\Support\Paths;

beforeEach(function (): void {
    cleanupGeneratedPluginFixture();
});

afterEach(function (): void {
    cleanupGeneratedPluginFixture();
});

function generatedPluginPath(): string
{
    return Paths::pluginsPath('acme/test-starter');
}

function cleanupGeneratedPluginFixture(): void
{
    File::deleteDirectory(generatedPluginPath());
    DB::table('tp_plugins')->where('id', 'acme/test-starter')->delete();
}

it('clones the boilerplate template into a renamed plugin package', function (): void {
    $this->artisan('tp:plugin:make', [
        'vendor' => 'acme',
        'slug' => 'test-starter',
        'name' => 'Test Starter',
        '--source' => 'local',
        '--namespace' => 'Acme\\TestStarter',
        '--description' => 'A generated plugin for tests.',
    ])
        ->expectsOutputToContain('Plugin generated successfully.')
        ->expectsOutputToContain(generatedPluginPath())
        ->expectsOutputToContain('Package: acme/test-starter')
        ->expectsOutputToContain('Template source: local')
        ->assertSuccessful();

    expect(File::exists(generatedPluginPath().'/composer.json'))->toBeTrue();
    expect(File::exists(generatedPluginPath().'/src/TestStarterServiceProvider.php'))->toBeTrue();

    $composerJson = File::get(generatedPluginPath().'/composer.json');
    $manifestJson = File::get(generatedPluginPath().'/tentapress.json');
    $composer = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);
    $manifest = json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
    $providerPhp = File::get(generatedPluginPath().'/src/TestStarterServiceProvider.php');
    $updateControllerPhp = File::get(generatedPluginPath().'/src/Http/Admin/UpdateController.php');

    expect($composerJson)->toContain('"name": "acme/test-starter"');
    expect($composer['autoload']['psr-4']['Acme\\TestStarter\\'] ?? null)->toBe('src/');
    expect($manifest['id'] ?? null)->toBe('acme/test-starter');
    expect($manifest['name'] ?? null)->toBe('Test Starter');
    expect($manifest['description'] ?? null)->toBe('A generated plugin for tests.');
    expect($providerPhp)->toContain('namespace Acme\\TestStarter;');
    expect($providerPhp)->toContain("loadViewsFrom(__DIR__.'/../resources/views', 'acme-test-starter')");
    expect($updateControllerPhp)->toContain("return to_route('tp.test-starter.index')");

    expect(
        DB::table('tp_plugins')->where('id', 'acme/test-starter')->exists()
    )->toBeTrue();
});

it('fails when the destination plugin directory already exists', function (): void {
    File::ensureDirectoryExists(generatedPluginPath());

    $this->artisan('tp:plugin:make', [
        'vendor' => 'acme',
        'slug' => 'test-starter',
        'name' => 'Test Starter',
        '--source' => 'local',
        '--namespace' => 'Acme\\TestStarter',
        '--description' => 'A generated plugin for tests.',
    ])
        ->expectsOutputToContain('Destination already exists:')
        ->assertFailed();
});

it('uses Packagist as the default expectation and falls back to local when unavailable', function (): void {
    Http::fake([
        'https://repo.packagist.org/*' => Http::response([], 404),
    ]);

    $this->artisan('tp:plugin:make', [
        'vendor' => 'acme',
        'slug' => 'test-starter',
        'name' => 'Test Starter',
        '--namespace' => 'Acme\\TestStarter',
        '--description' => 'A generated plugin for tests.',
    ])
        ->expectsOutputToContain('Template source: local')
        ->expectsOutputToContain('Packagist template unavailable - falling back to local boilerplate source.')
        ->assertSuccessful();
});
