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
}
