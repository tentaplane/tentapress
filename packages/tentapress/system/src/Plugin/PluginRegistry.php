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

    public function isInstalled(string $id): bool
    {
        try {
            $plugin = $this->pluginForId($id);
        } catch (\Throwable) {
            return false;
        }

        return $this->isPluginInstalled($plugin);
    }

    /**
     * @param  array<string,mixed>  $plugin
     */
    public function isPluginInstalled(array $plugin): bool
    {
        $provider = trim((string) ($plugin['provider'] ?? ''));
        $path = (string) ($plugin['path'] ?? '');
        $manifest = $this->decodeManifest($plugin['manifest'] ?? null);

        if ($provider === '') {
            return false;
        }

        return $this->providerClassAvailable($provider, $path, $manifest);
    }

    public function enable(string $id): void
    {
        $this->assertId($id);

        $plugin = $this->pluginForId($id);
        $provider = trim((string) ($plugin['provider'] ?? ''));
        $path = (string) ($plugin['path'] ?? '');
        $manifest = $this->decodeManifest($plugin['manifest'] ?? null);

        $this->assertProviderAvailable($id, $provider, $path, $manifest);

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
        $plugins = DB::table('tp_plugins')->get(['id', 'provider', 'path', 'manifest'])->map(fn ($row): array => (array) $row)->all();
        if ($plugins === []) {
            return 0;
        }

        $enableIds = [];

        foreach ($plugins as $plugin) {
            $id = (string) ($plugin['id'] ?? '');
            if ($id === '') {
                continue;
            }

            if (! $this->isPluginInstalled($plugin)) {
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
            ->get(['id', 'provider', 'path', 'manifest'])
            ->map(fn ($row): array => (array) $row)
            ->all();

        if ($plugins === []) {
            return ['enabled' => 0, 'skipped' => 0, 'skipped_ids' => []];
        }

        $enableIds = [];
        $skippedIds = [];

        foreach ($plugins as $plugin) {
            $id = (string) ($plugin['id'] ?? '');
            if ($id === '') {
                continue;
            }

            if (! $this->isPluginInstalled($plugin)) {
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

        foreach ($rows as $row) {
            $data = (array) $row;
            $id = (string) ($data['id'] ?? '');
            $provider = trim((string) ($data['provider'] ?? ''));
            $path = (string) ($data['path'] ?? '');

            if ($id === '' || $provider === '') {
                continue;
            }

            $manifest = [];

            try {
                $manifest = json_decode((string) ($data['manifest'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                $manifest = [];
            }

            if (! $this->isPluginInstalled($data)) {
                continue;
            }

            $enabled[] = [
                'id' => $id,
                'provider' => $provider,
                'path' => $path,
                'version' => (string) ($data['version'] ?? ''),
                'manifest' => $manifest,
            ];
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

            if (! $this->isPluginInstalled([
                'provider' => $manifest->provider,
                'path' => $manifest->path,
                'manifest' => $manifest->data,
            ])) {
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

    /**
     * @return array<string,mixed>
     */
    private function pluginForId(string $id): array
    {
        $plugin = DB::table('tp_plugins')->where('id', $id)->first(['provider', 'path', 'manifest']);
        throw_if($plugin === null, RuntimeException::class, "Plugin not found in tp_plugins: {$id}. Did you run `php artisan tp:plugins sync`?");

        return (array) $plugin;
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeManifest(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string,mixed>  $manifest
     */
    private function assertProviderAvailable(string $id, string $provider, string $path, array $manifest): void
    {
        throw_if($provider === '', RuntimeException::class, "Plugin {$id} does not declare a service provider.");
        throw_if(! $this->isPluginInstalled([
            'provider' => $provider,
            'path' => $path,
            'manifest' => $manifest,
        ]), RuntimeException::class, "Plugin {$id} is not installed. Run: composer require {$id}");
    }



    private function assertId(string $id): void
    {
        $id = trim($id);

        throw_if($id === '' || ! Str::contains($id, '/'), RuntimeException::class, "Invalid plugin id '{$id}'. Expected vendor/name.");
    }

    /**
     * @param  array<string,mixed>  $manifest
     */
    private function providerClassAvailable(string $provider, string $path, array $manifest): bool
    {
        if (class_exists($provider)) {
            return true;
        }

        foreach ($this->providerClassCandidates($provider, $path, $manifest) as $candidate) {
            if (! is_file($candidate)) {
                continue;
            }

            try {
                require_once $candidate;
            } catch (\Throwable) {
                continue;
            }

            if (class_exists($provider, false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string,mixed>  $manifest
     * @return array<int,string>
     */
    private function providerClassCandidates(string $provider, string $path, array $manifest): array
    {
        $base = base_path($path);
        $providerPath = trim((string) ($manifest['provider_path'] ?? ''));
        $shortClass = $this->shortClassName($provider);
        $candidates = [];

        if ($providerPath !== '') {
            $candidates[] = $base.'/'.ltrim($providerPath, '/');
        }

        if ($shortClass !== '') {
            $candidates[] = $base.'/src/'.$shortClass.'.php';
            $candidates[] = $base.'/'.$shortClass.'.php';
        }

        return array_values(array_unique($candidates));
    }

    private function shortClassName(string $class): string
    {
        $class = trim($class);
        if ($class === '') {
            return '';
        }

        $offset = strrpos($class, '\\');

        return $offset === false ? $class : substr($class, $offset + 1);
    }
}
