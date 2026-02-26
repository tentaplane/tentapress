<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Env;

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
        return self::cachePath('tp_plugins');
    }

    public static function themeCachePath(): string
    {
        return self::cachePath('tp_theme');
    }

    /**
     * @return array<int,string>
     */
    public static function pluginSearchRoots(): array
    {
        $vendorNamespaces = config('tentapress.plugin_vendor_namespaces', ['tentapress']);

        return self::manifestSearchRoots(self::pluginsPath(), is_array($vendorNamespaces) ? $vendorNamespaces : []);
    }

    /**
     * @return array<int,string>
     */
    public static function themeSearchRoots(): array
    {
        return array_values(array_filter(
            [self::themesPath()],
            is_dir(...)
        ));
    }

    /**
     * @return array<int,string>
     */
    private static function manifestSearchRoots(string $firstRoot, array $vendorNamespaces): array
    {
        $roots = [$firstRoot];

        foreach ($vendorNamespaces as $namespace) {
            $namespace = trim((string) $namespace);
            if ($namespace !== '') {
                $roots[] = base_path('vendor/'.$namespace);
            }
        }

        return array_values(array_filter($roots, is_dir(...)));
    }

    private static function cachePath(string $cacheKey): string
    {
        $suffix = self::parallelTestToken();
        $filename = $cacheKey.($suffix === '' ? '' : '.'.$suffix).'.php';

        return base_path('bootstrap/cache/'.$filename);
    }

    private static function parallelTestToken(): string
    {
        $token = Request::server('TEST_TOKEN') ?? Env::get('TEST_TOKEN', getenv('TEST_TOKEN'));
        $token = trim((string) $token);

        if ($token === '') {
            return '';
        }

        return preg_replace('/[^A-Za-z0-9._-]/', '-', $token) ?? '';
    }
}
