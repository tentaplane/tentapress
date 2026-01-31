<?php

declare(strict_types=1);

use TentaPress\AdminShell\Http\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
});
