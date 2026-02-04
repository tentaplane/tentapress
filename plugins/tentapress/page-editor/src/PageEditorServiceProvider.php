<?php

declare(strict_types=1);

namespace TentaPress\PageEditor;

use Illuminate\Support\ServiceProvider;
use TentaPress\PageEditor\Render\PageDocumentRenderer;
use TentaPress\System\Plugin\PluginRegistry;

final class PageEditorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->isPluginEnabled()) {
            return;
        }

        $this->app->singleton(PageDocumentRenderer::class);

        $this->app->bind('tp.page_editor.render', function (): callable {
            return function (array $document): string {
                $renderer = $this->app->make(PageDocumentRenderer::class);

                return $renderer->render($document);
            };
        });

        $this->app->bind('tp.pages.editor.view', fn () => 'tentapress-page-editor::editor');
        $this->app->bind('tp.posts.editor.view', fn () => 'tentapress-page-editor::editor');
    }

    public function boot(): void
    {
        if (! $this->isPluginEnabled()) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-page-editor');
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

        return isset($enabled['tentapress/page-editor']);
    }
}
