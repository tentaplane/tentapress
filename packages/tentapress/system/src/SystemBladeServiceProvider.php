<?php

declare(strict_types=1);

namespace TentaPress\System;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TentaPress\System\Plugin\PluginAssetRegistry;

final class SystemBladeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Blade::directive('tpPluginAssets', fn ($expression): string => "<?php echo app('".PluginAssetRegistry::class."')->tags({$expression}); ?>");
        Blade::directive('tpPluginStyles', fn ($expression): string => "<?php echo app('".PluginAssetRegistry::class."')->styleTags({$expression}); ?>");
        Blade::directive('tpPluginScripts', fn ($expression): string => "<?php echo app('".PluginAssetRegistry::class."')->scriptTags({$expression}); ?>");
    }
}
