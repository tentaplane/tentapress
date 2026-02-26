<?php

declare(strict_types=1);

namespace TentaPress\System\Theme;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use RuntimeException;
use TentaPress\System\Support\Paths;
use Throwable;

final class ThemeManager
{
    public const VIEW_NAMESPACE = 'tp-theme';

    /**
     * Register the active theme view namespace (if active theme exists).
     * Safe to call multiple times.
     */
    public function registerActiveThemeViews(): void
    {
        $active = $this->activeTheme();

        if (! $active) {
            return;
        }

        $viewsPath = Paths::themesPath($active['path'].'/views');

        if (! is_dir($viewsPath)) {
            return;
        }

        // Bind namespace to active theme views
        View::addNamespace(self::VIEW_NAMESPACE, $viewsPath);
    }

    /**
     * Register the active theme service provider (if declared).
     */
    public function registerActiveThemeProvider(): void
    {
        $active = $this->activeTheme();

        if (! $active) {
            return;
        }

        $manifest = $active['manifest'] ?? [];

        if (! is_array($manifest)) {
            return;
        }

        $provider = $manifest['provider'] ?? null;

        if (! is_string($provider) || $provider === '') {
            return;
        }

        $app = app();

        if ($app->getProvider($provider)) {
            return;
        }

        if (! class_exists($provider)) {
            $providerPath = $manifest['provider_path'] ?? null;

            if (is_string($providerPath) && $providerPath !== '') {
                $fullPath = Paths::themesPath($active['path'].'/'.$providerPath);

                if (is_file($fullPath)) {
                    require_once $fullPath;
                }
            }
        }

        if (! class_exists($provider)) {
            return;
        }

        $app->register($provider);
    }

    /**
     * Activate a theme by id and rebuild cache.
     */
    public function activate(string $themeId): void
    {
        $theme = DB::table('tp_themes')->where('id', $themeId)->first();
        throw_unless($theme, RuntimeException::class, "Theme not found in tp_themes: {$themeId}. Did you run `php artisan tp:themes sync`?");

        // Store in tp_settings as JSON string to keep flexible
        DB::table('tp_settings')->updateOrInsert(
            ['key' => 'active_theme'],
            [
                'value' => json_encode($themeId, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->writeCache();
        $this->registerActiveThemeViews();
    }

    public function clearCache(): void
    {
        $path = Paths::themeCachePath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function writeCache(): void
    {
        $themeId = $this->activeThemeIdFromDb();

        if ($themeId === null) {
            $this->clearCache();

            return;
        }

        $row = DB::table('tp_themes')->where('id', $themeId)->first();

        if (! $row) {
            $this->clearCache();

            return;
        }

        try {
            $manifest = json_decode((string) ($row->manifest ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $manifest = [];
        }

        $payload = [
            'generated_at' => now()->toISOString(),
            'active' => [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'version' => (string) ($row->version ?? ''),
                'path' => (string) $row->path,
                'manifest' => $manifest,
            ],
        ];

        $path = Paths::themeCachePath();
        $dir = dirname($path);

        throw_if(! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir), RuntimeException::class, "Unable to create cache directory: {$dir}");

        $php = "<?php\n\nreturn ".var_export($payload, true).";\n";
        $written = file_put_contents($path, $php, LOCK_EX);

        throw_if($written === false, RuntimeException::class, "Unable to write theme cache file: {$path}");
    }

    /**
     * @return array{id:string,name:string,version:string,path:string,manifest:array}|null
     */
    public function activeTheme(): ?array
    {
        $cache = $this->readCache();

        if ($cache !== null) {
            return $cache;
        }

        // DB fallback
        $themeId = $this->activeThemeIdFromDb();

        if ($themeId === null) {
            return null;
        }

        $row = DB::table('tp_themes')->where('id', $themeId)->first();

        if (! $row) {
            return null;
        }

        try {
            $manifest = json_decode((string) ($row->manifest ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $manifest = [];
        }

        return [
            'id' => (string) $row->id,
            'name' => (string) $row->name,
            'version' => (string) ($row->version ?? ''),
            'path' => (string) $row->path,
            'manifest' => $manifest,
        ];
    }

    /**
     * Resolve the best layout view name for a given layout key.
     * Returns null if no theme layout is available.
     */
    public function layoutView(string $layoutKey): ?string
    {
        $layoutKey = trim($layoutKey) !== '' ? trim($layoutKey) : 'default';

        $candidates = [
            self::VIEW_NAMESPACE.'::layouts.'.$layoutKey,
            self::VIEW_NAMESPACE.'::layouts.page',
        ];

        foreach ($candidates as $view) {
            if (View::exists($view)) {
                return $view;
            }
        }

        return null;
    }

    /**
     * Returns layouts declared by the ACTIVE theme manifest.
     *
     * Manifest format:
     * "layouts": [
     *   { "key": "default", "label": "Default" },
     *   { "key": "landing", "label": "Landing" }
     * ]
     *
     * @return array<int,array{key:string,label:string}>
     */
    public function activeLayouts(): array
    {
        $active = $this->activeTheme();

        if (! $active) {
            return [];
        }

        $manifest = $active['manifest'] ?? [];

        if (! is_array($manifest)) {
            return [];
        }

        $layouts = $manifest['layouts'] ?? [];

        if (! is_array($layouts)) {
            return [];
        }

        $out = [];

        foreach ($layouts as $entry) {
            // Allow either array entries or simple strings
            if (is_string($entry)) {
                $key = trim($entry);

                if ($key === '') {
                    continue;
                }

                $out[] = [
                    'key' => $key,
                    'label' => ucfirst($key),
                ];

                continue;
            }

            if (! is_array($entry)) {
                continue;
            }

            $key = isset($entry['key']) ? trim((string) $entry['key']) : '';

            if ($key === '') {
                continue;
            }

            $label = isset($entry['label']) ? trim((string) $entry['label']) : '';

            if ($label === '') {
                $label = ucfirst($key);
            }

            $out[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        // Ensure "default" always exists as the first option
        $hasDefault = false;

        foreach ($out as $l) {
            if (($l['key'] ?? '') === 'default') {
                $hasDefault = true;
                break;
            }
        }

        if (! $hasDefault) {
            array_unshift($out, ['key' => 'default', 'label' => 'Default']);
        }

        // De-dupe by key, keep first occurrence (so default stays first)
        $deduped = [];

        foreach ($out as $l) {
            $k = (string) ($l['key'] ?? '');

            if ($k === '' || isset($deduped[$k])) {
                continue;
            }

            $deduped[$k] = $l;
        }

        return array_values($deduped);
    }

    /**
     * Convenience: is there an active theme?
     */
    public function hasActiveTheme(): bool
    {
        return $this->activeTheme() !== null;
    }

    /**
     * @return array{id:string,name:string,version:string,path:string,manifest:array}|null
     */
    private function readCache(): ?array
    {
        $path = Paths::themeCachePath();

        if (! is_file($path)) {
            return null;
        }

        $data = @include $path;

        if (! is_array($data) || ! isset($data['active']) || ! is_array($data['active'])) {
            return null;
        }

        $a = $data['active'];

        if (! isset($a['id'], $a['path'])) {
            return null;
        }

        return [
            'id' => (string) $a['id'],
            'name' => isset($a['name']) ? (string) $a['name'] : '',
            'version' => isset($a['version']) ? (string) $a['version'] : '',
            'path' => (string) $a['path'],
            'manifest' => isset($a['manifest']) && is_array($a['manifest']) ? $a['manifest'] : [],
        ];
    }

    private function activeThemeIdFromDb(): ?string
    {
        try {
            $row = DB::table('tp_settings')->where('key', 'active_theme')->first();

            if (! $row) {
                return null;
            }

            $value = $row->value ?? null;

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_string($decoded) && $decoded !== '') {
                    return $decoded;
                }

                if (is_array($decoded) && isset($decoded['id']) && is_string($decoded['id'])) {
                    return $decoded['id'];
                }
            }

            if (is_array($value) && isset($value['id']) && is_string($value['id'])) {
                return $value['id'];
            }

            return is_scalar($value) ? (string) $value : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
