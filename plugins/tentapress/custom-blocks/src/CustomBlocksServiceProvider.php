<?php

declare(strict_types=1);

namespace TentaPress\CustomBlocks;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\CustomBlocks\Discovery\ThemeSingleFileBlockKit;

final class CustomBlocksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeSingleFileBlockKit::class);
    }

    public function boot(): void
    {
        if (! app()->bound(BlockRegistry::class)) {
            return;
        }

        $registry = app()->make(BlockRegistry::class);

        if (! $registry instanceof BlockRegistry) {
            return;
        }

        $this->app->make(ThemeSingleFileBlockKit::class)->register($registry);
    }
}
