<?php

declare(strict_types=1);

namespace TentaPress\Workflow;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use TentaPress\Workflow\Console\PublishScheduledWorkflowCommand;
use TentaPress\Workflow\Services\WorkflowAuditLogger;
use TentaPress\Workflow\Services\WorkflowCapabilitySeeder;
use TentaPress\Workflow\Services\WorkflowFormComposer;
use TentaPress\Workflow\Services\WorkflowManager;
use TentaPress\Workflow\Services\WorkflowResourceResolver;

final class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WorkflowCapabilitySeeder::class);
        $this->app->singleton(WorkflowResourceResolver::class);
        $this->app->singleton(WorkflowAuditLogger::class);
        $this->app->singleton(WorkflowManager::class);
        $this->app->singleton(WorkflowFormComposer::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-workflow');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->app->make(WorkflowCapabilitySeeder::class)->run();
        });

        View::composer([
            'tentapress-pages::pages.form',
            'tentapress-posts::posts.form',
        ], WorkflowFormComposer::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishScheduledWorkflowCommand::class,
            ]);

            $this->app->booted(function (): void {
                $this->app->make(Schedule::class)
                    ->command('tp:workflow:publish-scheduled')
                    ->everyMinute()
                    ->withoutOverlapping();
            });
        }
    }
}
