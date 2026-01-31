<?php

declare(strict_types=1);

namespace TentaPress\Themes\Tailwind;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class TailwindThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('tp-theme::*', function (): void {
            $hotFile = public_path('themes/tentapress/tailwind/hot');

            if (is_file($hotFile)) {
                Vite::useHotFile($hotFile);
            }
        });
    }
}
