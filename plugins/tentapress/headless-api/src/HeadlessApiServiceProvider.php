<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi;

use Illuminate\Support\ServiceProvider;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;
use TentaPress\HeadlessApi\Support\BlogBaseResolver;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\HeadlessApi\Support\SeoPayloadBuilder;

final class HeadlessApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiErrorResponder::class);
        $this->app->singleton(BlogBaseResolver::class);
        $this->app->singleton(ContentPayloadBuilder::class);
        $this->app->singleton(SeoPayloadBuilder::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
