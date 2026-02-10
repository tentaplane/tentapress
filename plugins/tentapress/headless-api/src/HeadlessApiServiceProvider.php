<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi;

use Illuminate\Support\ServiceProvider;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;

final class HeadlessApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentPayloadBuilder::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
