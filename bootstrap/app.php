<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$cacheDir = __DIR__.'/cache';
$servicesCachePath = $cacheDir.'/services.php';

if (is_file($servicesCachePath)) {
    $services = require $servicesCachePath;
    $providers = $services['providers'] ?? [];

    $hasMissingTentapressProvider = false;

    if (is_array($providers)) {
        foreach ($providers as $provider) {
            if (! is_string($provider) || ! str_contains($provider, 'TentaPress\\')) {
                continue;
            }

            if (! class_exists($provider)) {
                $hasMissingTentapressProvider = true;

                break;
            }
        }
    }

    if ($hasMissingTentapressProvider) {
        foreach (['services.php', 'packages.php', 'tp_plugins.php'] as $cacheFile) {
            $path = $cacheDir.'/'.$cacheFile;

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
