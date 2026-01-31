<?php

declare(strict_types=1);

namespace TentaPress\MediaBunny;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\MediaBunny\Support\BunnyMediaUrlGenerator;

final class MediaBunnyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ((string) config('tentapress.media.url_driver', 'local') !== 'bunny') {
            return;
        }

        $this->app->singleton(MediaUrlGenerator::class, BunnyMediaUrlGenerator::class);
    }
}
