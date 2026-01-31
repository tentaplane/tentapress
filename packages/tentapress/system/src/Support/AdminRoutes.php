<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

use Closure;
use Illuminate\Support\Facades\Route;

final class AdminRoutes
{
    /**
     * Wrap routes in /admin prefix, tp.* name, and tp.admin middleware group.
     *
     * @param  Closure():void  $routes
     */
    public static function group(Closure $routes): void
    {
        Route::middleware('tp.admin')
            ->prefix('admin')
            ->as('tp.')
            ->group($routes);
    }
}
