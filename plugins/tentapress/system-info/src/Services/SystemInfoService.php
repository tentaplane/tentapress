<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use TentaPress\System\Plugin\PluginRegistry;

final readonly class SystemInfoService
{
    public function __construct(
        private PluginRegistry $plugins,
    ) {
    }

    /**
     * Full on-screen report (safe to display).
     *
     * @return array<string,mixed>
     */
    public function report(): array
    {
        $enabledPlugins = $this->enabledPlugins();
        $theme = $this->activeTheme();

        $php = [
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'memory_limit' => (string) ini_get('memory_limit'),
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
            'max_execution_time' => (string) ini_get('max_execution_time'),
            'extensions' => $this->importantExtensions(),
        ];

        $laravel = [
            'laravel_version' => app()->version(),
            'app_env' => (string) config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'app_url' => (string) config('app.url'),
        ];

        $runtime = [
            'database' => [
                'default' => (string) config('database.default'),
                'driver' => (string) config('database.connections.'.config('database.default').'.driver'),
            ],
            'cache' => [
                'default' => (string) config('cache.default'),
            ],
            'session' => [
                'driver' => (string) config('session.driver'),
            ],
            'queue' => [
                'default' => (string) config('queue.default'),
            ],
        ];

        $storage = [
            'storage_path' => storage_path(),
            'storage_writable' => is_writable(storage_path()),
            'bootstrap_cache_path' => base_path('bootstrap/cache'),
            'bootstrap_cache_writable' => is_writable(base_path('bootstrap/cache')),
        ];

        $paths = [
            'base_path' => base_path(),
            'public_path' => public_path(),
        ];

        return [
            'generated_at' => now()->toISOString(),
            'php' => $php,
            'laravel' => $laravel,
            'runtime' => $runtime,
            'storage' => $storage,
            'paths' => $paths,
            'tentapress' => [
                'active_theme' => $theme,
                'enabled_plugins' => $enabledPlugins,
            ],
        ];
    }

    /**
     * Downloadable diagnostics (keep it conservative; do not include secrets).
     *
     * @return array<string,mixed>
     */
    public function diagnostics(): array
    {
        $r = $this->report();

        // Extra: basic filesystem checks; still safe.
        $r['filesystem'] = [
            'storage' => $this->dirHealth(storage_path()),
            'bootstrap_cache' => $this->dirHealth(base_path('bootstrap/cache')),
            'logs' => $this->dirHealth(storage_path('logs')),
        ];

        // Extra: lightweight “sanitised config summary”
        $r['config_summary'] = [
            'app' => [
                'env' => (string) config('app.env'),
                'debug' => (bool) config('app.debug'),
                'timezone' => (string) config('app.timezone'),
            ],
            'database' => [
                'default' => (string) config('database.default'),
                'connections' => array_keys((array) config('database.connections', [])),
            ],
            'cache' => [
                'default' => (string) config('cache.default'),
                'stores' => array_keys((array) config('cache.stores', [])),
            ],
            'session' => [
                'driver' => (string) config('session.driver'),
            ],
        ];

        return $r;
    }

    /**
     * @return array<int,array{id:string,version:string,path:string,provider:string}>
     */
    private function enabledPlugins(): array
    {
        $cache = $this->plugins->readCache();

        $out = [];
        foreach ($cache as $id => $info) {
            $out[] = [
                'id' => (string) $id,
                'version' => (string) ($info['version'] ?? ''),
                'path' => (string) ($info['path'] ?? ''),
                'provider' => (string) ($info['provider'] ?? ''),
            ];
        }

        usort($out, static fn (array $a, array $b): int => strcmp($a['id'], $b['id']));

        return $out;
    }

    private function activeTheme(): string
    {
        try {
            $row = DB::table('tp_settings')->where('key', 'active_theme')->first();
            if (! $row) {
                return '—';
            }

            $value = $row->value ?? null;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_string($decoded)) {
                    return $decoded;
                }
                if (is_array($decoded) && isset($decoded['id']) && is_string($decoded['id'])) {
                    return $decoded['id'];
                }
            }

            if (is_array($value) && isset($value['id']) && is_string($value['id'])) {
                return $value['id'];
            }

            return is_scalar($value) ? (string) $value : '—';
        } catch (\Throwable) {
            // If DB isn't ready yet or sqlite missing etc
            return '—';
        }
    }

    /**
     * @return array<int,array{key:string,loaded:bool}>
     */
    private function importantExtensions(): array
    {
        $keys = [
            'pdo',
            'pdo_sqlite',
            'pdo_mysql',
            'mbstring',
            'openssl',
            'json',
            'ctype',
            'fileinfo',
            'curl',
            'intl',
            'gd',
            'imagick',
            'zip',
        ];

        $out = [];
        foreach ($keys as $k) {
            $out[] = ['key' => $k, 'loaded' => extension_loaded($k)];
        }

        return $out;
    }

    /**
     * @return array<string,mixed>
     */
    private function dirHealth(string $path): array
    {
        return [
            'path' => $path,
            'exists' => File::exists($path),
            'is_dir' => File::isDirectory($path),
            'writable' => is_writable($path),
            'readable' => is_readable($path),
        ];
    }
}
