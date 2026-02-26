<?php

declare(strict_types=1);

namespace TentaPress\Builder;

use Illuminate\Support\ServiceProvider;
use TentaPress\Builder\Support\PreviewSnapshotStore;
use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;
use TentaPress\System\Plugin\PluginRegistry;

final class BuilderServiceProvider extends ServiceProvider
{
    private static bool $autoloadRegistered = false;

    public function register(): void
    {
        $this->registerNamespaceAutoloader();

        if (! $this->isPluginEnabled()) {
            return;
        }

        $this->app->singleton(PreviewSnapshotStore::class);

        $this->app->afterResolving(EditorDriverRegistry::class, function (EditorDriverRegistry $registry): void {
            $this->registerEditorDriver($registry);
        });

        if ($this->app->bound(EditorDriverRegistry::class)) {
            $this->registerEditorDriver($this->app->make(EditorDriverRegistry::class));
        }
    }

    public function boot(): void
    {
        if (! $this->isPluginEnabled()) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-builder');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }

    private function isPluginEnabled(): bool
    {
        if (! class_exists(PluginRegistry::class) || ! $this->app->bound(PluginRegistry::class)) {
            return true;
        }

        $registry = $this->app->make(PluginRegistry::class);
        if (! method_exists($registry, 'readCache')) {
            return true;
        }

        $enabled = $registry->readCache();

        if ($enabled === []) {
            return true;
        }

        return isset($enabled['tentapress/builder']);
    }

    private function registerNamespaceAutoloader(): void
    {
        if (self::$autoloadRegistered) {
            return;
        }

        spl_autoload_register(static function (string $class): void {
            $prefix = 'TentaPress\\Builder\\';
            if (! str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            if ($relative === false || $relative === '') {
                return;
            }

            $path = __DIR__.'/'.str_replace('\\', '/', $relative).'.php';

            if (is_file($path)) {
                require_once $path;
            }
        });

        self::$autoloadRegistered = true;
    }

    private function registerEditorDriver(EditorDriverRegistry $registry): void
    {
        $registry->register(new EditorDriverDefinition(
            id: 'builder',
            label: 'Visual Builder',
            description: 'Drag and drop visual canvas with live preview.',
            storage: 'blocks',
            pagesView: 'tentapress-builder::editor',
            postsView: 'tentapress-builder::editor',
            usesBlocksEditor: false,
            sortOrder: 30,
        ));
    }
}
