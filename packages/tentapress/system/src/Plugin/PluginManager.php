<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Contracts\Foundation\Application;

final class PluginManager
{
    private array $registeredProviders = [];

    private array $registeredAutoloadPrefixes = [];

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

            $path = is_array($info) ? (string) ($info['path'] ?? '') : '';

            $this->registerProviderOnce($provider, $id, $path);
        }
    }

    private function registerProviderOnce(string $providerClass, string $pluginId, string $pluginPath): void
    {
        if (isset($this->registeredProviders[$providerClass])) {
            return;
        }

        if (! class_exists($providerClass)) {
            $this->registerPathAutoloader($providerClass, $pluginId, $pluginPath);
        }

        if (! class_exists($providerClass)) {
            logger()->warning("Skipping enabled plugin '{$pluginId}' because provider class was not found: {$providerClass}");
            return;
        }

        $this->app->register($providerClass);
        $this->registeredProviders[$providerClass] = true;
    }

    private function registerPathAutoloader(string $providerClass, string $pluginId, string $pluginPath): void
    {
        $namespacePrefix = $this->providerNamespacePrefix($providerClass);
        $relativePluginPath = trim($pluginPath, '/');

        if ($namespacePrefix === null) {
            return;
        }

        if (isset($this->registeredAutoloadPrefixes[$namespacePrefix])) {
            return;
        }

        $sourcePaths = $this->pluginSourcePaths($pluginId, $relativePluginPath);
        if ($sourcePaths === []) {
            return;
        }

        spl_autoload_register(static function (string $class) use ($namespacePrefix, $sourcePaths): void {
            if (! str_starts_with($class, $namespacePrefix)) {
                return;
            }

            $relativeClass = substr($class, strlen($namespacePrefix));
            if (! is_string($relativeClass) || $relativeClass === '') {
                return;
            }

            $relativeClassPath = '/'.str_replace('\\', '/', $relativeClass).'.php';

            foreach ($sourcePaths as $sourcePath) {
                $classPath = $sourcePath.$relativeClassPath;
                if (is_file($classPath)) {
                    require_once $classPath;

                    return;
                }
            }
        });

        $this->registeredAutoloadPrefixes[$namespacePrefix] = true;
    }

    /**
     * @return array<int,string>
     */
    private function pluginSourcePaths(string $pluginId, string $relativePluginPath): array
    {
        $paths = [];

        if ($relativePluginPath !== '') {
            $paths[] = base_path($relativePluginPath.'/src');
        }

        $normalizedPluginId = trim($pluginId, '/');
        if ($normalizedPluginId !== '') {
            $paths[] = base_path('vendor/'.$normalizedPluginId.'/src');
        }

        return array_values(array_unique(array_filter($paths, is_dir(...))));
    }

    private function providerNamespacePrefix(string $providerClass): ?string
    {
        $separatorPosition = strrpos($providerClass, '\\');

        if ($separatorPosition === false) {
            return null;
        }

        return substr($providerClass, 0, $separatorPosition + 1);
    }
}
