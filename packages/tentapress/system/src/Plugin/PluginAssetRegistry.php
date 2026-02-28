<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Support\Arr;

final class PluginAssetRegistry
{
    private array $manifestCache = [];

    public function __construct(
        private readonly PluginRegistry $plugins,
    ) {
    }

    /**
     * @return array{scripts:array<int,string>,styles:array<int,string>}
     */
    public function assets(string $pluginId, string $context = 'admin'): array
    {
        if (! $this->isEnabled($pluginId)) {
            return ['scripts' => [], 'styles' => []];
        }

        $manifest = $this->manifestFor($pluginId);
        if ($manifest === null) {
            return ['scripts' => [], 'styles' => []];
        }

        $entries = $this->assetEntries($pluginId, $context);
        if ($entries === null) {
            return ['scripts' => [], 'styles' => []];
        }

        $scripts = [];
        $styles = [];

        foreach ($entries['scripts'] as $key) {
            $item = $manifest[$key] ?? null;
            if (! is_array($item)) {
                continue;
            }

            $file = $item['file'] ?? null;
            if (is_string($file)) {
                $scripts[] = $this->assetUrl($pluginId, $file);
            }

            $css = $item['css'] ?? [];
            if (is_array($css)) {
                foreach ($css as $cssFile) {
                    if (is_string($cssFile)) {
                        $styles[] = $this->assetUrl($pluginId, $cssFile);
                    }
                }
            }
        }

        foreach ($entries['styles'] as $key) {
            $item = $manifest[$key] ?? null;
            if (! is_array($item)) {
                continue;
            }

            $file = $item['file'] ?? null;
            if (is_string($file)) {
                $styles[] = $this->assetUrl($pluginId, $file);
            }
        }

        return [
            'scripts' => array_values(array_unique($scripts)),
            'styles' => array_values(array_unique($styles)),
        ];
    }

    public function tags(string $pluginId, string $context = 'admin'): string
    {
        $assets = $this->assets($pluginId, $context);

        $tags = [];
        foreach ($assets['styles'] as $href) {
            $tags[] = '<link rel="stylesheet" href="'.e($href).'">';
        }
        foreach ($assets['scripts'] as $src) {
            $tags[] = '<script type="module" src="'.e($src).'"></script>';
        }

        return $tags === [] ? '' : implode("\n", $tags);
    }

    public function styleTags(string $pluginId, string $context = 'admin'): string
    {
        $assets = $this->assets($pluginId, $context);
        if ($assets['styles'] === []) {
            return '';
        }

        $tags = [];
        foreach ($assets['styles'] as $href) {
            $tags[] = '<link rel="stylesheet" href="'.e($href).'">';
        }

        return implode("\n", $tags);
    }

    public function scriptTags(string $pluginId, string $context = 'admin'): string
    {
        $assets = $this->assets($pluginId, $context);
        if ($assets['scripts'] === []) {
            return '';
        }

        $tags = [];
        foreach ($assets['scripts'] as $src) {
            $tags[] = '<script type="module" src="'.e($src).'"></script>';
        }

        return implode("\n", $tags);
    }

    /**
     * @return array{enabled:bool,manifest_path:?string,manifest_found:bool,assets:array{scripts:array<int,string>,styles:array<int,string>}}
     */
    public function debugInfo(string $pluginId, string $context = 'admin'): array
    {
        $this->ensurePublicAssets($pluginId);
        $enabled = $this->plugins->readCache();
        $pluginPath = is_array($enabled[$pluginId] ?? null) ? (string) ($enabled[$pluginId]['path'] ?? '') : '';
        $manifestPath = $this->manifestPath($pluginId, $pluginPath);
        $manifestFound = $manifestPath !== null && is_file($manifestPath);

        return [
            'enabled' => $this->isEnabled($pluginId),
            'manifest_path' => $manifestPath,
            'manifest_found' => $manifestFound,
            'assets' => $this->assets($pluginId, $context),
        ];
    }

    private function isEnabled(string $pluginId): bool
    {
        $enabled = $this->plugins->readCache();

        return isset($enabled[$pluginId]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function manifestFor(string $pluginId): ?array
    {
        if (array_key_exists($pluginId, $this->manifestCache)) {
            return $this->manifestCache[$pluginId];
        }

        $this->ensurePublicAssets($pluginId);

        $enabled = $this->plugins->readCache();
        $pluginPath = is_array($enabled[$pluginId] ?? null) ? (string) ($enabled[$pluginId]['path'] ?? '') : '';
        $path = $this->manifestPath($pluginId, $pluginPath);
        if ($path === null || ! is_file($path)) {
            $this->manifestCache[$pluginId] = null;

            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $this->manifestCache[$pluginId] = null;

            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $this->manifestCache[$pluginId] = null;

            return null;
        }

        $this->manifestCache[$pluginId] = $decoded;

        return $decoded;
    }

    /**
     * @return array{scripts:array<int,string>,styles:array<int,string>}|null
     */
    private function assetEntries(string $pluginId, string $context): ?array
    {
        $enabled = $this->plugins->readCache();
        $manifest = $enabled[$pluginId]['manifest'] ?? null;
        if (! is_array($manifest)) {
            return null;
        }

        $assets = $manifest['assets'] ?? null;
        if (! is_array($assets)) {
            return null;
        }

        $contextAssets = $assets[$context] ?? null;
        if (! is_array($contextAssets)) {
            return null;
        }

        $scripts = Arr::wrap($contextAssets['scripts'] ?? []);
        $styles = Arr::wrap($contextAssets['styles'] ?? []);

        return [
            'scripts' => array_values(array_filter($scripts, is_string(...))),
            'styles' => array_values(array_filter($styles, is_string(...))),
        ];
    }

    private function manifestPath(string $pluginId, string $pluginPath = ''): ?string
    {
        $parts = array_values(array_filter(explode('/', $pluginId)));
        if (count($parts) !== 2) {
            return null;
        }

        [$vendor, $name] = $parts;

        if ($pluginPath !== '') {
            $pluginBuild = rtrim($pluginPath, '/').'/build';
            $pluginManifest = $pluginBuild.'/manifest.json';
            if (is_file($pluginManifest)) {
                return $pluginManifest;
            }

            $pluginViteManifest = $pluginBuild.'/.vite/manifest.json';
            if (is_file($pluginViteManifest)) {
                return $pluginViteManifest;
            }
        }

        $root = public_path('plugins/'.$vendor.'/'.$name.'/build');
        $primary = $root.'/manifest.json';
        if (is_file($primary)) {
            return $primary;
        }

        $viteManifest = $root.'/.vite/manifest.json';
        if (is_file($viteManifest)) {
            return $viteManifest;
        }

        return $primary;
    }

    private function assetUrl(string $pluginId, string $file): string
    {
        $parts = array_values(array_filter(explode('/', $pluginId)));
        [$vendor, $name] = $parts;

        $relativePath = 'plugins/'.$vendor.'/'.$name.'/build/'.$file;
        $version = $this->assetVersion(public_path($relativePath));
        $separator = str_contains($relativePath, '?') ? '&' : '?';

        return asset($relativePath).($version === null ? '' : $separator.'v='.$version);
    }

    private function assetVersion(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $hash = md5_file($path);

        if (! is_string($hash) || $hash === '') {
            return null;
        }

        return substr($hash, 0, 12);
    }

    private function ensurePublicAssets(string $pluginId): void
    {
        $parts = array_values(array_filter(explode('/', $pluginId)));
        if (count($parts) !== 2) {
            return;
        }

        [$vendor, $name] = $parts;

        $publicRoot = public_path('plugins/'.$vendor.'/'.$name.'/build');
        if (is_file($publicRoot.'/manifest.json') || is_file($publicRoot.'/.vite/manifest.json')) {
            return;
        }

        $enabled = $this->plugins->readCache();
        $pluginPath = $enabled[$pluginId]['path'] ?? null;
        if (! is_string($pluginPath) || $pluginPath === '') {
            return;
        }

        if (app()->bound(PluginAssetPublisher::class)) {
            $publisher = app()->make(PluginAssetPublisher::class);
            $publisher->publish($pluginId, $pluginPath);
        }
    }
}
