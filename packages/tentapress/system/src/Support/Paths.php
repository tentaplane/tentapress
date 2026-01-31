<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

final class Paths
{
    public static function pluginsPath(string $append = ''): string
    {
        $base = base_path('plugins');

        return $append === '' ? $base : $base.DIRECTORY_SEPARATOR.ltrim($append, DIRECTORY_SEPARATOR);
    }

    public static function themesPath(string $append = ''): string
    {
        $base = base_path('themes');

        return $append === '' ? $base : $base.DIRECTORY_SEPARATOR.ltrim($append, DIRECTORY_SEPARATOR);
    }

    public static function pluginCachePath(): string
    {
        return base_path('bootstrap/cache/tp_plugins.php');
    }

    public static function themeCachePath(): string
    {
        return base_path('bootstrap/cache/tp_theme.php');
    }

    /**
     * @return array<int,string>
     */
    public static function pluginSearchRoots(): array
    {
        return self::manifestSearchRoots(self::pluginsPath());
    }

    /**
     * @return array<int,string>
     */
    public static function themeSearchRoots(): array
    {
        return self::manifestSearchRoots(self::themesPath());
    }

    /**
     * @return array<int,string>
     */
    private static function manifestSearchRoots(string $firstRoot): array
    {
        $roots = [$firstRoot];

        $vendorNamespaces = config('tentapress.plugin_vendor_namespaces', ['tentapress']);
        if (is_array($vendorNamespaces)) {
            foreach ($vendorNamespaces as $namespace) {
                $namespace = trim((string) $namespace);
                if ($namespace !== '') {
                    $roots[] = base_path('vendor/'.$namespace);
                }
            }
        }

        return array_values(array_filter($roots, static fn (string $path): bool => is_dir($path)));
    }
}
