<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use TentaPress\System\Support\Paths;

final class PluginRegistry
{
    /**
     * Plugins that should not be disabled by plugins disable --all
     * unless --force is used.
     */
    public const PROTECTED_PLUGIN_IDS = [
        // You may treat admin shell as a plugin later; keep the ID stable.
        'tentapress/admin-shell',
        // Users is critical once installed.
        'tentapress/users',
        // System is not a plugin row, but listed here for clarity.
        'tentapress/system',
    ];

    /**
     * Scan filesystem for tentapress.json and return manifests.
     *
     * @return array<string,PluginManifest> keyed by plugin id
     */
    public function discoverManifests(): array
    {
        $paths = $this->pluginSearchPaths();
        if ($paths === []) {
            return [];
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($paths)
            ->name('tentapress.json')
            ->ignoreDotFiles(true);

        $manifests = [];

        foreach ($finder as $file) {
            $manifestPath = $file->getRealPath();
            if ($manifestPath === false) {
                continue;
            }

            $manifest = PluginManifest::fromFile($manifestPath);
            $type = $manifest->data['type'] ?? null;
            if ($type !== null && (string) $type === 'theme') {
                continue;
            }

            // If duplicates exist, last one wins (but this is a problem you should fix).
            $manifests[$manifest->id] = $manifest;
        }

        ksort($manifests);

        return $manifests;
    }

    /**
     * Sync filesystem manifests into tp_plugins.
     *
     * @return int number of records upserted
     */
    public function sync(): int
    {
        $manifests = $this->discoverManifests();
        if ($manifests === []) {
            return 0;
        }

        $now = now();
        $existingIds = DB::table('tp_plugins')->pluck('id')->map(strval(...))->all();

        $rows = [];
        foreach ($manifests as $m) {
            $rows[] = [
                'id' => $m->id,
                'version' => $m->version,
                'provider' => $m->provider,
                'path' => $m->path,
                'manifest' => json_encode($m->data, JSON_THROW_ON_ERROR),
                // don't force enabled/disabled on sync; preserve existing state
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        // Upsert: preserve enabled status by only updating these fields.
        DB::table('tp_plugins')->upsert(
            $rows,
            ['id'],
            ['version', 'provider', 'path', 'manifest', 'updated_at']
        );

        $this->enableDefaultPlugins($manifests, $existingIds);

        return count($rows);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listAll(): array
    {
        $rows = DB::table('tp_plugins')
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        return $rows;
    }

    public function enable(string $id): void
    {
        $this->assertId($id);

        $provider = $this->providerForId($id);
        $this->assertProviderAvailable($id, $provider);

        $updated = DB::table('tp_plugins')
            ->where('id', $id)
            ->update(['enabled' => 1, 'updated_at' => now()]);

        throw_if($updated === 0, RuntimeException::class, "Plugin not found in tp_plugins: {$id}. Did you run `php artisan tp:plugins sync`?");

        $this->publishAssetsFor($id);
    }

    public function disable(string $id, bool $force = false): void
    {
        $this->assertId($id);

        throw_if(! $force && in_array($id, self::PROTECTED_PLUGIN_IDS, true), RuntimeException::class, "Refusing to disable protected plugin '{$id}'. Use --force to override.");

        $updated = DB::table('tp_plugins')
            ->where('id', $id)
            ->update(['enabled' => 0, 'updated_at' => now()]);

        throw_if($updated === 0, RuntimeException::class, "Plugin not found in tp_plugins: {$id}. Did you run `php artisan tp:plugins sync`?");

        $this->unpublishAssetsFor($id);
    }

    public function enableAll(): int
    {
        $plugins = DB::table('tp_plugins')->get(['id', 'provider'])->map(fn ($row): array => (array) $row)->all();
        if ($plugins === []) {
            return 0;
        }

        $enableIds = [];

        foreach ($plugins as $plugin) {
            $id = (string) ($plugin['id'] ?? '');
            $provider = trim((string) ($plugin['provider'] ?? ''));
            if ($id === '' || $provider === '') {
                continue;
            }

            if (! class_exists($provider)) {
                continue;
            }

            $enableIds[] = $id;
        }

        if ($enableIds === []) {
            return 0;
        }

        $updated = DB::table('tp_plugins')
            ->whereIn('id', $enableIds)
            ->update(['enabled' => 1, 'updated_at' => now()]);

        foreach ($enableIds as $id) {
            $this->publishAssetsFor((string) $id);
        }

        return $updated;
    }

    /**
     * @return array{enabled:int,skipped:int,skipped_ids:array<int,string>}
     */
    public function enableDefaults(): array
    {
        $defaults = $this->defaultPluginIds();
        if ($defaults === []) {
            return ['enabled' => 0, 'skipped' => 0, 'skipped_ids' => []];
        }

        $plugins = DB::table('tp_plugins')
            ->whereIn('id', $defaults)
            ->get(['id', 'provider'])
            ->map(fn ($row): array => (array) $row)
            ->all();

        if ($plugins === []) {
            return ['enabled' => 0, 'skipped' => 0, 'skipped_ids' => []];
        }

        $enableIds = [];
        $skippedIds = [];

        foreach ($plugins as $plugin) {
            $id = (string) ($plugin['id'] ?? '');
            $provider = trim((string) ($plugin['provider'] ?? ''));
            if ($id === '' || $provider === '') {
                continue;
            }

            if (! class_exists($provider)) {
                $skippedIds[] = $id;

                continue;
            }

            $enableIds[] = $id;
        }

        $enabled = 0;

        if ($enableIds !== []) {
            $enabled = DB::table('tp_plugins')
                ->whereIn('id', $enableIds)
                ->update(['enabled' => 1, 'updated_at' => now()]);

            foreach ($enableIds as $id) {
                $this->publishAssetsFor((string) $id);
            }
        }

        return [
            'enabled' => $enabled,
            'skipped' => count($skippedIds),
            'skipped_ids' => $skippedIds,
        ];
    }

    private function publishAssetsFor(string $id): void
    {
        if (! app()->bound(PluginAssetPublisher::class)) {
            return;
        }

        $row = DB::table('tp_plugins')
            ->where('id', $id)
            ->first(['id', 'path']);

        if (! is_object($row)) {
            return;
        }

        $pluginId = (string) ($row->id ?? '');
        $path = (string) ($row->path ?? '');
        if ($pluginId === '' || $path === '') {
            return;
        }

        $publisher = app()->make(PluginAssetPublisher::class);
        $publisher->publish($pluginId, $path);
    }

    private function unpublishAssetsFor(string $id): void
    {
        if (! app()->bound(PluginAssetPublisher::class)) {
            return;
        }

        $publisher = app()->make(PluginAssetPublisher::class);
        $publisher->unpublish($id);
    }

    public function disableAll(bool $force = false): int
    {
        $q = DB::table('tp_plugins');

        if (! $force) {
            $q->whereNotIn('id', self::PROTECTED_PLUGIN_IDS);
        }

        $ids = $q->pluck('id')->map(strval(...))->all();
        if ($ids === []) {
            return 0;
        }

        $updated = DB::table('tp_plugins')
            ->whereIn('id', $ids)
            ->update(['enabled' => 0, 'updated_at' => now()]);

        foreach ($ids as $id) {
            $this->unpublishAssetsFor((string) $id);
        }

        return $updated;
    }

    /**
     * Build cache from DB enabled plugins -> bootstrap/cache/tp_plugins.php
     */
    public function writeCache(): void
    {
        $rows = DB::table('tp_plugins')
            ->where('enabled', 1)
            ->orderBy('id')
            ->get(['id', 'provider', 'path', 'version', 'manifest'])
            ->all();

        $enabled = [];
        $missingIds = [];

        foreach ($rows as $row) {
            $data = (array) $row;
            $id = (string) ($data['id'] ?? '');
            $provider = trim((string) ($data['provider'] ?? ''));

            if ($id === '' || $provider === '' || ! class_exists($provider)) {
                if ($id !== '') {
                    $missingIds[] = $id;
                }

                continue;
            }

            $manifest = [];

            try {
                $manifest = json_decode((string) ($data['manifest'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                $manifest = [];
            }

            $enabled[] = [
                'id' => $id,
                'provider' => $provider,
                'path' => (string) ($data['path'] ?? ''),
                'version' => (string) ($data['version'] ?? ''),
                'manifest' => $manifest,
            ];
        }

        if ($missingIds !== []) {
            DB::table('tp_plugins')
                ->whereIn('id', $missingIds)
                ->update(['enabled' => 0, 'updated_at' => now()]);
        }

        $payload = [
            'generated_at' => now()->toISOString(),
            'plugins' => [],
        ];

        foreach ($enabled as $p) {
            $payload['plugins'][$p['id']] = [
                'provider' => $p['provider'],
                'path' => $p['path'],
                'version' => $p['version'],
                'manifest' => $p['manifest'],
            ];
        }

        if (app()->bound(PluginAssetPublisher::class)) {
            $publisher = app()->make(PluginAssetPublisher::class);
            foreach ($enabled as $p) {
                $id = (string) ($p['id'] ?? '');
                $path = (string) ($p['path'] ?? '');
                if ($id !== '' && $path !== '') {
                    $publisher->publish($id, $path);
                }
            }
        }

        $path = Paths::pluginCachePath();
        $dir = dirname($path);

        throw_if(! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir), RuntimeException::class, "Unable to create cache directory: {$dir}");

        $php = "<?php\n\nreturn ".var_export($payload, true).";\n";
        $written = file_put_contents($path, $php);

        throw_if($written === false, RuntimeException::class, "Unable to write plugin cache file: {$path}");
    }

    public function clearCache(): void
    {
        $path = Paths::pluginCachePath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * @return array<string, array{provider:string,path:string,version:string,manifest:array}>
     */
    public function readCache(): array
    {
        $path = Paths::pluginCachePath();
        if (! is_file($path)) {
            return [];
        }

        $data = require $path;

        if (! is_array($data) || ! isset($data['plugins']) || ! is_array($data['plugins'])) {
            return [];
        }

        $plugins = $data['plugins'];

        return $plugins;
    }

    /**
     * @param  array<string,PluginManifest>  $manifests
     * @param  array<int,string>  $existingIds
     */
    private function enableDefaultPlugins(array $manifests, array $existingIds): void
    {
        $defaultIds = $this->defaultPluginIds();
        if ($defaultIds === []) {
            return;
        }

        $manifestIds = array_keys($manifests);
        $newIds = array_values(array_diff($manifestIds, $existingIds));
        if ($newIds === []) {
            return;
        }

        $defaultNewIds = array_values(array_intersect($defaultIds, $newIds));
        if ($defaultNewIds === []) {
            return;
        }

        $enableIds = [];

        foreach ($defaultNewIds as $id) {
            $manifest = $manifests[$id] ?? null;
            if (! $manifest instanceof PluginManifest) {
                continue;
            }

            $provider = trim($manifest->provider);
            if ($provider === '' || ! class_exists($provider)) {
                continue;
            }

            $enableIds[] = $id;
        }

        if ($enableIds === []) {
            return;
        }

        DB::table('tp_plugins')
            ->whereIn('id', $enableIds)
            ->update(['enabled' => 1, 'updated_at' => now()]);
    }

    /**
     * @return array<int,string>
     */
    private function defaultPluginIds(): array
    {
        $defaults = config('tentapress.default_plugins', []);
        if (! is_array($defaults)) {
            return [];
        }

        $normalized = array_values(array_filter(array_map(static fn (mixed $id): string => trim((string) $id), $defaults)));

        return array_values(array_unique($normalized));
    }

    /**
     * @return array<int,string>
     */
    private function pluginSearchPaths(): array
    {
        return Paths::pluginSearchRoots();
    }

    private function providerForId(string $id): string
    {
        $plugin = DB::table('tp_plugins')->where('id', $id)->first(['provider']);
        throw_if($plugin === null, RuntimeException::class, "Plugin not found in tp_plugins: {$id}. Did you run `php artisan tp:plugins sync`?");

        $provider = trim((string) ($plugin->provider ?? ''));
        throw_if($provider === '', RuntimeException::class, "Plugin {$id} does not declare a service provider.");

        return $provider;
    }

    private function assertProviderAvailable(string $id, string $provider): void
    {
        throw_if(! class_exists($provider), RuntimeException::class, "Plugin {$id} is not installed. Run: composer require {$id}");
    }

    private function assertId(string $id): void
    {
        $id = trim($id);

        throw_if($id === '' || ! Str::contains($id, '/'), RuntimeException::class, "Invalid plugin id '{$id}'. Expected vendor/name.");
    }
}
