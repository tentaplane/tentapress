<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Admin\Menu;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use TentaPress\System\Support\Paths;

final class MenuBuilder
{
    /**
     * Cached flat items (unfiltered).
     */
    private ?array $cached = null;

    public function __construct(
        private readonly Router $router,
    ) {
    }

    /**
     * Build sidebar menu items (2-level) from enabled plugin manifests, filtered by user capabilities.
     *
     * Flat manifest shape (admin.menus):
     * - label (string, required)
     * - route (string, required)
     * - capability (string|null)
     * - icon (string|null)
     * - position (int)
     * - parent (string|null) // label of parent group, e.g. "Users"
     *
     * Output tree shape:
     * [
     *   [
     *     'label' => 'Users',
     *     'route' => 'tp.users.index'|null,
     *     'url' => 'https://.../admin/users'|null,
     *     'capability' => 'manage_users'|null,
     *     'icon' => 'users'|null,
     *     'position' => 70,
     *     'active' => bool,
     *     'children' => [
     *       [
     *         'label' => 'Roles',
     *         'route' => 'tp.roles.index',
     *         'url' => '...',
     *         'capability' => 'manage_roles',
     *         'icon' => null,
     *         'position' => 20,
     *         'parent' => 'Users',
     *         'active' => bool,
     *         'children' => []
     *       ]
     *     ]
     *   ]
     * ]
     *
     * @return array<int,array<string,mixed>>
     */
    public function build(mixed $user = null): array
    {
        $flat = $this->cached ?? $this->buildFlat();

        // Cache flat list (not user-filtered)
        $this->cached ??= $flat;

        // Filter by user capability (flat)
        $flat = $this->filterForUser($flat, $user);

        // Convert to 2-level tree
        return $this->toTree($flat);
    }

    /**
     * Build the flat list of items from core + manifests.
     *
     * @return array<int,array<string,mixed>>
     */
    private function buildFlat(): array
    {
        $items = [];

        // Core menu items (always available)
        $items[] = [
            'label' => 'Dashboard',
            'route' => 'tp.dashboard',
            'url' => $this->router->has('tp.dashboard') ? route('tp.dashboard') : null,
            'capability' => null,
            'icon' => null,
            'position' => 0,
            'parent' => null,
        ];

        foreach ($this->enabledPluginManifests() as $manifest) {
            $menus = Arr::get($manifest, 'admin.menus', []);
            if (!is_array($menus)) {
                continue;
            }

            foreach ($menus as $menu) {
                if (!is_array($menu)) {
                    continue;
                }

                $label = trim((string) ($menu['label'] ?? ''));
                $route = trim((string) ($menu['route'] ?? ''));

                if ($label === '' || $route === '') {
                    continue;
                }

                // Only include items that point to a known route (avoids broken sidebar links)
                if (!$this->router->has($route)) {
                    continue;
                }

                $capability = isset($menu['capability']) && trim((string) $menu['capability']) !== '' ? trim((string) $menu['capability']) : null;
                $icon = isset($menu['icon']) && trim((string) $menu['icon']) !== '' ? trim((string) $menu['icon']) : null;
                $position = isset($menu['position']) && is_numeric($menu['position']) ? (int) $menu['position'] : 50;
                $parent = isset($menu['parent']) && trim((string) $menu['parent']) !== '' ? trim((string) $menu['parent']) : null;

                $items[] = [
                    'label' => $label,
                    'route' => $route,
                    'url' => route($route),
                    'capability' => $capability,
                    'icon' => $icon,
                    'position' => $position,
                    'parent' => $parent,
                ];
            }
        }

        // Sort flat: position asc, then label asc
        usort($items, static function (array $a, array $b): int {
            $pa = (int) ($a['position'] ?? 50);
            $pb = (int) ($b['position'] ?? 50);

            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $items;
    }

    /**
     * @param array<int,array<string,mixed>> $items Flat, filtered
     * @return array<int,array<string,mixed>>
     */
    private function toTree(array $items): array
    {
        $currentRoute = request()->route()?->getName() ?? '';

        // Normalize items with active + children keys
        $normalized = [];
        foreach ($items as $item) {
            $route = (string) ($item['route'] ?? '');
            $normalized[] = [
                'label' => (string) ($item['label'] ?? ''),
                'route' => $route !== '' ? $route : null,
                'url' => $item['url'] ?? null,
                'capability' => $item['capability'] ?? null,
                'icon' => $item['icon'] ?? null,
                'position' => (int) ($item['position'] ?? 50),
                'parent' => $item['parent'] ?? null,
                'active' => $route !== '' && $currentRoute !== '' ? $route === $currentRoute : false,
                'children' => [],
            ];
        }

        // Split into parents and children buckets
        $top = [];
        $childrenByParent = [];

        foreach ($normalized as $item) {
            $parentLabel = trim((string) ($item['parent'] ?? ''));

            if ($parentLabel !== '') {
                $childrenByParent[$parentLabel] ??= [];
                $childrenByParent[$parentLabel][] = $item;
                continue;
            }

            // Use label as key (unique enough for admin menus; also matches parent reference)
            $top[(string) $item['label']] = $item;
        }

        // Create implicit parents for children groups that don't exist as top-level items
        foreach ($childrenByParent as $parentLabel => $children) {
            if (isset($top[$parentLabel])) {
                continue;
            }

            $minPos = 50;
            foreach ($children as $c) {
                $p = (int) ($c['position'] ?? 50);
                if ($p < $minPos) {
                    $minPos = $p;
                }
            }

            $top[$parentLabel] = [
                'label' => $parentLabel,
                'route' => null,
                'url' => null,
                'capability' => null,
                'icon' => null,
                'position' => $minPos,
                'parent' => null,
                'active' => false,
                'children' => [],
            ];
        }

        // Attach children, sort them, and mark parent active if any child active
        foreach ($childrenByParent as $parentLabel => $children) {
            $children = $this->sortMenuItems($children);

            $parent = $top[$parentLabel];
            $parent['children'] = $children;

            foreach ($children as $child) {
                if (!empty($child['active'])) {
                    $parent['active'] = true;
                    break;
                }
            }

            $top[$parentLabel] = $parent;
        }

        // Sort top-level items
        $tree = $this->sortMenuItems(array_values($top));

        // Ensure children are always sorted (even if parent had none during attach)
        foreach ($tree as $i => $node) {
            if (!empty($node['children']) && is_array($node['children'])) {
                $node['children'] = $this->sortMenuItems($node['children']);
                $tree[$i] = $node;
            }
        }

        return $tree;
    }

    /**
     * @param array<int,array<string,mixed>> $items
     * @return array<int,array<string,mixed>>
     */
    private function sortMenuItems(array $items): array
    {
        usort($items, static function (array $a, array $b): int {
            $pa = (int) ($a['position'] ?? 50);
            $pb = (int) ($b['position'] ?? 50);

            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return $items;
    }

    /**
     * @param  array<int,array<string,mixed>>  $items
     * @return array<int,array<string,mixed>>
     */
    private function filterForUser(array $items, mixed $user): array
    {
        // If there is no user (e.g. login page), show nothing.
        if (!$user) {
            return [];
        }

        $isSuper = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() === true;

        $out = [];

        foreach ($items as $item) {
            $cap = $item['capability'] ?? null;

            if ($cap === null || $cap === '') {
                $out[] = $item;
                continue;
            }

            if ($isSuper) {
                $out[] = $item;
                continue;
            }

            if (method_exists($user, 'hasCapability') && $user->hasCapability((string) $cap) === true) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * Returns an array of plugin manifests (decoded JSON arrays) for enabled plugins.
     *
     * We prefer reading the plugin cache file to stay fast and avoid re-discovery.
     *
     * @return array<int,array<string,mixed>>
     */
    private function enabledPluginManifests(): array
    {
        $cachePath = Paths::pluginCachePath();

        // If cache missing, fall back to scanning plugins/*/*/tentapress.json
        if (!is_file($cachePath)) {
            return $this->scanPluginManifestsFallback();
        }

        $cache = require $cachePath;
        if (!is_array($cache)) {
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

        if (!is_array($plugins)) {
            return $this->scanPluginManifestsFallback();
        }

        $manifests = [];

        foreach ($plugins as $plugin) {
            if (!is_array($plugin)) {
                continue;
            }

            $id = (string) ($plugin['id'] ?? '');
            if ($id === '') {
                continue;
            }

            // If enabled list exists, enforce it
            if ($enabledIds !== [] && !in_array($id, $enabledIds, true)) {
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
            return $this->scanPluginManifestsFallback();
        }

        return $manifests;
    }

    /**
     * @param  array<mixed>  $cache
     */
    private function looksLikePluginList(array $cache): bool
    {
        // Detect list of plugin arrays: [ ['id'=>...], ['id'=>...], ... ]
        if ($cache === []) {
            return false;
        }

        $first = $cache[array_key_first($cache)] ?? null;

        return is_array($first) && array_key_exists('id', $first);
    }

    /**
     * Fallback scan: plugins/x/x/tentapress.json
     *
     * @return array<int,array<string,mixed>>
     */
    private function scanPluginManifestsFallback(): array
    {
        $manifests = [];

        foreach ($this->pluginSearchRoots() as $root) {
            // Only scan two levels deep: vendor/name/tentapress.json
            foreach (glob($root . '/*/*/tentapress.json') ?: [] as $file) {
                $decoded = $this->decodeJsonFile($file);
                if ($decoded !== null) {
                    $manifests[] = $decoded;
                }
            }
        }

        return $manifests;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function decodeJsonFile(string $path): ?array
    {
        if (!File::exists($path) || !File::isFile($path)) {
            return null;
        }

        $raw = File::get($path);
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        return $this->decodeJson($raw);
    }

    /**
     * @return array<string,mixed>|null
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
     * @return array<int,string>
     */
    private function pluginSearchRoots(): array
    {
        $roots = [Paths::pluginsPath()];

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

    private function resolveManifestPath(string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return '';
        }

        $isAbsolute = Str::startsWith($path, '/') || preg_match('/^[A-Za-z]:\\//', $path) === 1;

        if ($isAbsolute) {
            return rtrim($path, '/') . '/tentapress.json';
        }

        return base_path($path . '/tentapress.json');
    }
}
