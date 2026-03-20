<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TentaPress\ContentTypes\ContentReference\ContentTypeEntryReferenceSource;
use TentaPress\ContentTypes\Http\Public\ArchiveController;
use TentaPress\ContentTypes\Http\Public\ShowController;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryEditorDriverResolver;
use TentaPress\ContentTypes\Services\ContentEntryFieldPresenter;
use TentaPress\ContentTypes\Services\ContentEntryFieldValueNormalizer;
use TentaPress\ContentTypes\Services\ContentEntryRelationResolver;
use TentaPress\ContentTypes\Services\ContentEntryRenderer;
use TentaPress\ContentTypes\Services\ContentEntrySlugger;
use TentaPress\ContentTypes\Services\ContentTypeApiTransformer;
use TentaPress\ContentTypes\Services\ContentTypeBasePathValidator;
use TentaPress\ContentTypes\Services\ContentTypeFormDataFactory;
use TentaPress\ContentTypes\Services\ContentFieldSchemaNormalizer;
use TentaPress\ContentTypes\Services\ContentTypesCapabilitySeeder;
use TentaPress\ContentTypes\Support\BlocksNormalizer;
use TentaPress\System\ContentReference\ContentReferenceRegistry;

final class ContentTypesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentTypeBasePathValidator::class);
        $this->app->singleton(ContentFieldSchemaNormalizer::class);
        $this->app->singleton(ContentEntrySlugger::class);
        $this->app->singleton(ContentEntryRelationResolver::class);
        $this->app->singleton(ContentEntryFieldValueNormalizer::class);
        $this->app->singleton(ContentEntryFieldPresenter::class);
        $this->app->singleton(ContentEntryEditorDriverResolver::class);
        $this->app->singleton(ContentTypeFormDataFactory::class);
        $this->app->singleton(ContentTypeApiTransformer::class);
        $this->app->singleton(ContentTypesCapabilitySeeder::class);
        $this->app->singleton(BlocksNormalizer::class);
        $this->app->singleton(ContentEntryRenderer::class);
        $this->app->singleton(ContentTypeEntryReferenceSource::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-content-types');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->registerPublicRoutes();

        $this->app->booted(function (): void {
            if ($this->app->bound(ContentReferenceRegistry::class)) {
                $this->app->make(ContentReferenceRegistry::class)->register(
                    $this->app->make(ContentTypeEntryReferenceSource::class)
                );
            }

            $this->app->make(ContentTypesCapabilitySeeder::class)->run();
        });
    }

    private function registerPublicRoutes(): void
    {
        if (! Schema::hasTable('tp_content_types')) {
            return;
        }

        /** @var array<int,TpContentType> $contentTypes */
        $contentTypes = TpContentType::query()
            ->orderBy('base_path')
            ->get()
            ->all();

        if ($contentTypes === []) {
            return;
        }

        Route::middleware('web')->group(function () use ($contentTypes): void {
            foreach ($contentTypes as $contentType) {
                $basePath = trim((string) $contentType->base_path, '/');

                if ($basePath === '') {
                    continue;
                }

                if ($contentType->archive_enabled) {
                    Route::get('/'.$basePath, ArchiveController::class)
                        ->defaults('contentTypeKey', (string) $contentType->key)
                        ->name('tp.public.content-types.archive.'.$contentType->key);
                }

                Route::get('/'.$basePath.'/{slug}', ShowController::class)
                    ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
                    ->defaults('contentTypeKey', (string) $contentType->key)
                    ->name('tp.public.content-types.show.'.$contentType->key);
            }
        });
    }
}
