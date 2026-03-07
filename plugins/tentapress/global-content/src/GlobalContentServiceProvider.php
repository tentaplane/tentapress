<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Services\GlobalContentBlockRegistrar;
use TentaPress\GlobalContent\Services\GlobalContentCapabilitySeeder;
use TentaPress\GlobalContent\Services\GlobalContentCycleValidator;
use TentaPress\GlobalContent\Services\GlobalContentReferenceExtractor;
use TentaPress\GlobalContent\Services\GlobalContentReferenceResolver;
use TentaPress\GlobalContent\Services\GlobalContentSlugger;
use TentaPress\GlobalContent\Services\GlobalContentUsageIndexer;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;

final class GlobalContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GlobalContentSlugger::class);
        $this->app->singleton(GlobalContentReferenceExtractor::class);
        $this->app->singleton(GlobalContentReferenceResolver::class);
        $this->app->singleton(GlobalContentCycleValidator::class);
        $this->app->singleton(GlobalContentUsageIndexer::class);
        $this->app->singleton(GlobalContentCapabilitySeeder::class);
        $this->app->singleton(GlobalContentBlockRegistrar::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-global-content');
        $this->loadViewsFrom(__DIR__.'/../resources/views/blocks', 'tentapress-blocks');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->seedCapability();
        $this->registerBlockDefinition();
        $this->registerUsageHooks();
        $this->registerBladeDirective();
    }

    private function seedCapability(): void
    {
        $this->app->booted(function (): void {
            $this->app->make(GlobalContentCapabilitySeeder::class)->run();
        });
    }

    private function registerBlockDefinition(): void
    {
        if (! $this->app->bound(BlockRegistry::class)) {
            return;
        }

        $registry = $this->app->make(BlockRegistry::class);

        if ($registry instanceof BlockRegistry) {
            $this->app->make(GlobalContentBlockRegistrar::class)->register($registry);
        }
    }

    private function registerUsageHooks(): void
    {
        if (class_exists(TpPage::class)) {
            TpPage::saved(function (TpPage $page): void {
                if ($this->shouldIndexPageUsage($page)) {
                    $this->app->make(GlobalContentUsageIndexer::class)->reindexPage($page);
                }
            });

            TpPage::deleted(function (TpPage $page): void {
                $this->app->make(GlobalContentUsageIndexer::class)->forget('page', (int) $page->id);
            });
        }

        if (class_exists(TpPost::class)) {
            TpPost::saved(function (TpPost $post): void {
                if ($this->shouldIndexPostUsage($post)) {
                    $this->app->make(GlobalContentUsageIndexer::class)->reindexPost($post);
                }
            });

            TpPost::deleted(function (TpPost $post): void {
                $this->app->make(GlobalContentUsageIndexer::class)->forget('post', (int) $post->id);
            });
        }

        if (class_exists(TpGlobalContent::class)) {
            TpGlobalContent::deleted(function (TpGlobalContent $content): void {
                $content->usages()->delete();
            });
        }
    }

    private function registerBladeDirective(): void
    {
        Blade::directive('tpGlobalContent', fn (string $expression): string => "<?php echo app('".GlobalContentReferenceResolver::class."')->renderPublishedBySlug((string) {$expression}); ?>");
    }

    private function shouldIndexPageUsage(TpPage $page): bool
    {
        return Schema::hasTable('tp_global_content_usages') && (is_array($page->blocks) || is_array($page->content));
    }

    private function shouldIndexPostUsage(TpPost $post): bool
    {
        return Schema::hasTable('tp_global_content_usages') && (is_array($post->blocks) || is_array($post->content));
    }
}
