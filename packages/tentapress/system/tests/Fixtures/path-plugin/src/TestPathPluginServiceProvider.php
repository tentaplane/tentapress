<?php

declare(strict_types=1);

namespace Fixture\PathPlugin;

use Illuminate\Support\ServiceProvider;

final class TestPathPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config()->set('tentapress.tests.path_plugin_loaded', true);
    }
}
