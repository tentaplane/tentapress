<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

final class RuntimeCacheRefresher
{
    /**
     * Refreshes runtime caches used by long-running PHP runtimes (e.g. php-fpm with OPCache).
     *
     * @return array{opcache_available:bool,opcache_reset:bool,invalidated_files:int}
     */
    public function refreshAfterPluginChange(): array
    {
        clearstatcache();

        return $this->refreshOpcache([
            base_path('bootstrap/cache/tp_plugins.php'),
            base_path('bootstrap/cache/packages.php'),
            base_path('bootstrap/cache/services.php'),
        ]);
    }

    /**
     * Refreshes runtime caches after theme sync/activation.
     *
     * @return array{opcache_available:bool,opcache_reset:bool,invalidated_files:int}
     */
    public function refreshAfterThemeChange(): array
    {
        clearstatcache();

        return $this->refreshOpcache([
            base_path('bootstrap/cache/tp_theme.php'),
        ]);
    }

    /**
     * @param  array<int,string>  $paths
     * @return array{opcache_available:bool,opcache_reset:bool,invalidated_files:int}
     */
    private function refreshOpcache(array $paths): array
    {
        $opcacheAvailable = function_exists('opcache_reset');
        if (! $opcacheAvailable) {
            return [
                'opcache_available' => false,
                'opcache_reset' => false,
                'invalidated_files' => 0,
            ];
        }

        $reset = false;

        try {
            $reset = opcache_reset();
        } catch (\Throwable) {
            $reset = false;
        }

        $invalidated = 0;

        if (function_exists('opcache_invalidate')) {
            foreach ($paths as $path) {
                if (! is_file($path)) {
                    continue;
                }

                try {
                    if (opcache_invalidate($path, true)) {
                        $invalidated++;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return [
            'opcache_available' => true,
            'opcache_reset' => $reset,
            'invalidated_files' => $invalidated,
        ];
    }
}
