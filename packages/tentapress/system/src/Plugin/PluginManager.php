<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

final class PluginManager
{
    private array $registeredProviders = [];

    public function __construct(
        private readonly Application $app,
        private readonly PluginRegistry $registry,
    ) {
    }

    /**
     * Register enabled plugin service providers.
     *
     * Cache-first:
     * - If bootstrap/cache/tp_plugins.php exists, use it.
     * - Otherwise do nothing (keeps boot safe even before first migration).
     *
     * IMPORTANT: This expects plugin packages to be autoloadable (composer path repos + cda).
     */
    public function registerEnabledPluginProviders(): void
    {
        // Avoid double-run if provider register is called multiple times.
        if (! empty($this->registeredProviders)) {
            return;
        }

        $plugins = $this->registry->readCache();

        foreach ($plugins as $id => $info) {
            $provider = $info['provider'] ?? null;
            if (! is_string($provider) || $provider === '') {
                continue;
            }

            $this->registerProviderOnce($provider, $id);
        }
    }

    private function registerProviderOnce(string $providerClass, string $pluginId): void
    {
        if (isset($this->registeredProviders[$providerClass])) {
            return;
        }

        throw_unless(class_exists($providerClass), RuntimeException::class, "Enabled plugin '{$pluginId}' provider class not found: {$providerClass}. ".
        'Did you run `composer dump-autoload` after adding the plugin package?');

        $this->app->register($providerClass);
        $this->registeredProviders[$providerClass] = true;
    }
}
