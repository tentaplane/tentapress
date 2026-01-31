<?php

declare(strict_types=1);

namespace TentaPress\Posts;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use TentaPress\Posts\Console\PublishScheduledPostsCommand;
use TentaPress\Posts\Services\PostRenderer;
use TentaPress\Posts\Services\PostSlugger;
use TentaPress\Posts\Support\BlocksNormalizer;

final class PostsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostSlugger::class);
        $this->app->singleton(PostRenderer::class);
        $this->app->singleton(BlocksNormalizer::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tentapress-posts');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishScheduledPostsCommand::class,
            ]);

            $this->app->booted(function (): void {
                $this->app->make(Schedule::class)
                    ->command('tp:posts publish-scheduled')
                    ->everyMinute()
                    ->withoutOverlapping();
            });
        }
    }
}
