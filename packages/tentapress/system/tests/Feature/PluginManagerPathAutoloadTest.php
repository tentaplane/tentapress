<?php

declare(strict_types=1);

use Fixture\PathPlugin\TestPathPluginServiceProvider;
use Illuminate\Support\Facades\File;
use TentaPress\System\Plugin\PluginAssetPublisher;
use TentaPress\System\Plugin\PluginManager;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\System\Support\Paths;

it('registers enabled path plugin providers when composer autoload is unavailable', function (): void {
    config()->set('tentapress.tests.path_plugin_loaded', false);

    $providerClass = TestPathPluginServiceProvider::class;
    $cachePath = Paths::pluginCachePath();
    $originalCache = is_file($cachePath) ? file_get_contents($cachePath) : false;

    expect(class_exists($providerClass))->toBeFalse();

    $payload = [
        'generated_at' => now()->toISOString(),
        'plugins' => [
            'fixture/path-plugin' => [
                'provider' => $providerClass,
                'path' => 'packages/tentapress/system/tests/Fixtures/path-plugin',
                'version' => '0.0.1',
                'manifest' => [],
            ],
        ],
    ];

    file_put_contents($cachePath, "<?php\n\nreturn ".var_export($payload, true).";\n");

    app()->singleton(PluginAssetPublisher::class, static fn (): PluginAssetPublisher => new PluginAssetPublisher());

    try {
        $manager = new PluginManager(app(), app()->make(PluginRegistry::class));
        $manager->registerEnabledPluginProviders();

        expect(config('tentapress.tests.path_plugin_loaded'))->toBeTrue();
        expect(class_exists($providerClass))->toBeTrue();
    } finally {
        if ($originalCache === false) {
            @unlink($cachePath);
        } else {
            file_put_contents($cachePath, $originalCache);
        }
    }
});

it('falls back to vendor package paths when cached plugin paths are stale', function (): void {
    $providerClass = 'Fixture\\VendorPathPlugin\\VendorPathPluginServiceProvider';
    $cachePath = Paths::pluginCachePath();
    $originalCache = is_file($cachePath) ? file_get_contents($cachePath) : false;
    $fixtureRoot = base_path('vendor/fixture/vendor-path-plugin');
    $fixtureSourcePath = $fixtureRoot.'/src/VendorPathPluginServiceProvider.php';

    if (is_dir($fixtureRoot)) {
        File::deleteDirectory($fixtureRoot);
    }

    File::ensureDirectoryExists(dirname($fixtureSourcePath));
    File::put($fixtureSourcePath, <<<'PHP'
<?php

declare(strict_types=1);

namespace Fixture\VendorPathPlugin;

use Illuminate\Support\ServiceProvider;

final class VendorPathPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config()->set('tentapress.tests.vendor_path_plugin_loaded', true);
    }
}
PHP);

    expect(class_exists($providerClass))->toBeFalse();

    $payload = [
        'generated_at' => now()->toISOString(),
        'plugins' => [
            'fixture/vendor-path-plugin' => [
                'provider' => $providerClass,
                'path' => 'plugins/fixture/vendor-path-plugin',
                'version' => '0.0.1',
                'manifest' => [
                    'id' => 'fixture/vendor-path-plugin',
                ],
            ],
        ],
    ];

    file_put_contents($cachePath, "<?php\n\nreturn ".var_export($payload, true).";\n");

    app()->singleton(PluginAssetPublisher::class, static fn (): PluginAssetPublisher => new PluginAssetPublisher());

    try {
        $manager = new PluginManager(app(), app()->make(PluginRegistry::class));
        $manager->registerEnabledPluginProviders();

        expect(config('tentapress.tests.vendor_path_plugin_loaded'))->toBeTrue();
        expect(class_exists($providerClass))->toBeTrue();
    } finally {
        File::deleteDirectory($fixtureRoot);

        if ($originalCache === false) {
            @unlink($cachePath);
        } else {
            file_put_contents($cachePath, $originalCache);
        }
    }
});
