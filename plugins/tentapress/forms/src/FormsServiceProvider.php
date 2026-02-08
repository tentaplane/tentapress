<?php

declare(strict_types=1);

namespace TentaPress\Forms;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Forms\Discovery\FormsBlockKit;

final class FormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FormsBlockKit::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');

        if ($this->app->bound(BlockRegistry::class)) {
            $registry = $this->app->make(BlockRegistry::class);

            if ($registry instanceof BlockRegistry) {
                $this->app->make(FormsBlockKit::class)->register($registry);
            }
        }
    }
}
