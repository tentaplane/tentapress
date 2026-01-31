<?php

declare(strict_types=1);

namespace TentaPress\Import;

use Illuminate\Support\ServiceProvider;
use TentaPress\Import\Services\Importer;

final class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Importer::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tentapress-import');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
    }
}
