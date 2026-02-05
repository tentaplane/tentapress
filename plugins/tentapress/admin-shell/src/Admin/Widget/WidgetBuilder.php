<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Widget;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use TentaPress\AdminShell\Widgets\QuickStatsWidget;
use TentaPress\AdminShell\Widgets\SystemHealthWidget;
use TentaPress\AdminShell\Widgets\WelcomeWidget;
use TentaPress\System\Support\Paths;

final class WidgetBuilder implements WidgetBuilderContract
{
    /**
     * Cached definitions (unfiltered).
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $cached = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * Build widget instances filtered by user capability, sorted by priority.
     *
     * @return array<int, WidgetContract>
     */
    public function build(mixed $user = null): array
    {
        $definitions = $this->cached ?? $this->collectDefinitions();
        $this->cached ??= $definitions;

        if ($user === null) {
            return [];
        }

        $widgets = [];
        $isSuper = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() === true;

        foreach ($definitions as $def) {
            // Capability check
            $cap = $def['capability'] ?? null;
            if ($cap !== null && $cap !== '' && ! $isSuper) {
                if (! method_exists($user, 'hasCapability') || ! $user->hasCapability((string) $cap)) {
                    continue;
                }
            }

            // Instantiate widget
            $widget = $this->instantiate($def);
            if ($widget === null) {
                continue;
            }

            // Check if widget can render
            if (! $widget->canRender()) {
                continue;
            }

            $widgets[] = $widget;
        }

        // Sort by priority
        usort($widgets, static fn (WidgetContract $a, WidgetContract $b): int => $a->priority() <=> $b->priority());

        return $widgets;
    }

    /**
     * Collect widget definitions from core + enabled plugin manifests.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectDefinitions(): array
    {
        $definitions = [];

        // Core widgets (always available)
        $definitions = array_merge($definitions, $this->coreWidgets());

        // Plugin-contributed widgets
        foreach ($this->enabledPluginManifests() as $manifest) {
            $pluginId = (string) Arr::get($manifest, 'id', '');
            $widgets = Arr::get($manifest, 'admin.widgets', []);

            if (! is_array($widgets)) {
                continue;
            }

            foreach ($widgets as $widget) {
                if (! is_array($widget)) {
                    continue;
                }

                $id = trim((string) ($widget['id'] ?? ''));
                $class = trim((string) ($widget['class'] ?? ''));

                if ($id === '' || $class === '') {
                    continue;
                }

                // Ensure class exists
                if (! class_exists($class)) {
                    continue;
                }

                $definitions[] = [
                    'id' => $pluginId !== '' ? "{$pluginId}:{$id}" : $id,
                    'title' => trim((string) ($widget['title'] ?? $id)),
                    'class' => $class,
                    'position' => is_numeric($widget['position'] ?? null) ? (int) $widget['position'] : 50,
                    'capability' => isset($widget['capability']) && trim((string) $widget['capability']) !== ''
                        ? trim((string) $widget['capability'])
                        : null,
                    'colspan' => is_numeric($widget['colspan'] ?? null) ? (int) $widget['colspan'] : 1,
                ];
            }
        }

        return $definitions;
    }

    /**
     * Core widgets provided by admin-shell.
     *
     * @return array<int, array<string, mixed>>
     */
    private function coreWidgets(): array
    {
        return [
            [
                'id' => 'tentapress/admin-shell:welcome',
                'title' => 'Welcome',
                'class' => WelcomeWidget::class,
                'position' => 5,
                'capability' => null,
                'colspan' => 2,
            ],
            [
                'id' => 'tentapress/admin-shell:quick-stats',
                'title' => 'Quick Stats',
                'class' => QuickStatsWidget::class,
                'position' => 10,
                'capability' => null,
                'colspan' => 3,
            ],
            [
                'id' => 'tentapress/admin-shell:system-health',
                'title' => 'System',
                'class' => SystemHealthWidget::class,
                'position' => 5,
                'capability' => null,
                'colspan' => 1,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $def
     */
    private function instantiate(array $def): ?WidgetContract
    {
        $class = $def['class'] ?? '';
        if ($class === '' || ! class_exists($class)) {
            return null;
        }

        try {
            $widget = $this->container->make($class);

            if (! $widget instanceof WidgetContract) {
                return null;
            }

            // Allow overriding properties from manifest
            if (method_exists($widget, 'configure')) {
                $widget->configure($def);
            }

            return $widget;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Returns an array of plugin manifests (decoded JSON arrays) for enabled plugins.
     *
     * @return array<int, array<string, mixed>>
     */
    private function enabledPluginManifests(): array
    {
        $cachePath = Paths::pluginCachePath();

        // If cache missing, fall back to scanning plugins/*/*/tentapress.json
        if (! is_file($cachePath)) {
            $fromDatabase = $this->loadEnabledManifestsFromDatabase();
            if ($fromDatabase !== []) {
                return $fromDatabase;
            }

            return $this->scanPluginManifestsFallback();
        }

        $cache = require $cachePath;
        if (! is_array($cache)) {
            return $this->scanPluginManifestsFallback();
        }

        // Try common cache shapes
        $enabledIds = [];

        if (isset($cache['enabled']) && is_array($cache['enabled'])) {
            $enabledIds = array_values(array_filter(array_map(strval(...), $cache['enabled'])));
        }

        $plugins = null;

        if (isset($cache['plugins']) && is_array($cache['plugins'])) {
            $plugins = $cache['plugins'];
        } elseif (isset($cache['discovered']) && is_array($cache['discovered'])) {
            $plugins = $cache['discovered'];
        } elseif (isset($cache['items']) && is_array($cache['items'])) {
            $plugins = $cache['items'];
        } elseif ($this->looksLikePluginList($cache)) {
            // The cache itself might be a list
            $plugins = $cache;
        }

        if (! is_array($plugins)) {
            $fromDatabase = $this->loadEnabledManifestsFromDatabase();
            if ($fromDatabase !== []) {
                return $fromDatabase;
            }

            return $this->scanPluginManifestsFallback();
        }

        $manifests = [];

        foreach ($plugins as $plugin) {
            if (! is_array($plugin)) {
                continue;
            }

            $id = (string) ($plugin['id'] ?? '');
            if ($id === '') {
                continue;
            }

            // If enabled list exists, enforce it
            if ($enabledIds !== [] && ! in_array($id, $enabledIds, true)) {
                continue;
            }

            // Manifest may be stored inline
            if (isset($plugin['manifest']) && is_array($plugin['manifest'])) {
                $manifests[] = $plugin['manifest'];

                continue;
            }

            // Or as JSON string
            if (isset($plugin['manifest']) && is_string($plugin['manifest'])) {
                $decoded = $this->decodeJson((string) $plugin['manifest']);
                if ($decoded !== null) {
                    $manifests[] = $decoded;

                    continue;
                }
            }

            // Or as a manifest path
            $manifestPath = $plugin['manifest_path'] ?? null;
            if (is_string($manifestPath) && $manifestPath !== '' && is_file($manifestPath)) {
                $decoded = $this->decodeJsonFile($manifestPath);
                if ($decoded !== null) {
                    $manifests[] = $decoded;

                    continue;
                }
            }

            // Or derive from plugin path
            $path = (string) ($plugin['path'] ?? '');
            if ($path !== '') {
                $path = str_replace('\\', '/', $path);
                $candidate = $this->resolveManifestPath($path);
                $decoded = $this->decodeJsonFile($candidate);
                if ($decoded !== null) {
                    $manifests[] = $decoded;

                    continue;
                }
            }
        }

        if ($manifests === []) {
            $fromDatabase = $this->loadEnabledManifestsFromDatabase();
            if ($fromDatabase !== []) {
                return $fromDatabase;
            }

            return $this->scanPluginManifestsFallback();
        }

        return $manifests;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadEnabledManifestsFromDatabase(): array
    {
        if (! Schema::hasTable('tp_plugins')) {
            return [];
        }

        $rows = DB::table('tp_plugins')
            ->where('enabled', 1)
            ->orderBy('id')
            ->get(['manifest'])
            ->all();

        if ($rows === []) {
            return [];
        }

        $manifests = [];

        foreach ($rows as $row) {
            $raw = (string) ($row->manifest ?? '');
            if ($raw === '') {
                continue;
            }

            $decoded = $this->decodeJson($raw);
            if ($decoded !== null) {
                $manifests[] = $decoded;
            }
        }

        return $manifests;
    }

    /**
     * @param  array<mixed>  $cache
     */
    private function looksLikePluginList(array $cache): bool
    {
        if ($cache === []) {
            return false;
        }

        $first = $cache[array_key_first($cache)] ?? null;

        return is_array($first) && array_key_exists('id', $first);
    }

    /**
     * Fallback scan: plugins/x/x/tentapress.json
     *
     * @return array<int, array<string, mixed>>
     */
    private function scanPluginManifestsFallback(): array
    {
        $manifests = [];

        foreach ($this->pluginSearchRoots() as $root) {
            // Only scan two levels deep: vendor/name/tentapress.json
            foreach (glob($root.'/*/*/tentapress.json') ?: [] as $file) {
                $decoded = $this->decodeJsonFile($file);
                if ($decoded !== null) {
                    $manifests[] = $decoded;
                }
            }
        }

        return $manifests;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonFile(string $path): ?array
    {
        if (! File::exists($path) || ! File::isFile($path)) {
            return null;
        }

        $raw = File::get($path);
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        return $this->decodeJson($raw);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $raw): ?array
    {
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    private function pluginSearchRoots(): array
    {
        return Paths::pluginSearchRoots();
    }

    private function resolveManifestPath(string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return '';
        }

        $isAbsolute = Str::startsWith($path, '/') || preg_match('/^[A-Za-z]:\\//', $path) === 1;

        if ($isAbsolute) {
            return rtrim($path, '/').'/tentapress.json';
        }

        return base_path($path.'/tentapress.json');
    }
}
