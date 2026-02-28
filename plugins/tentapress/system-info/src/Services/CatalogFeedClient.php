<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class CatalogFeedClient
{
    /**
     * @return array{plugins:array<int,array<string,mixed>>,warning:?string}
     */
    public function fetch(): array
    {
        $url = trim((string) config('tentapress.catalog.url', ''));
        $localPlugins = $this->loadLocalPlugins();
        $warning = null;

        if ($url === '') {
            return [
                'plugins' => $localPlugins,
                'warning' => null,
            ];
        }

        $cacheKey = $this->cacheKey($url);
        $cacheTtl = max(30, (int) config('tentapress.catalog.cache_ttl_seconds', 900));
        $requireHttps = (bool) config('tentapress.catalog.require_https', true);

        try {
            $this->assertUrl($url, $requireHttps);
            $timeout = max(1, (int) config('tentapress.catalog.timeout_seconds', 5));

            $response = Http::connectTimeout($timeout)
                ->timeout($timeout)
                ->acceptJson()
                ->get($url);

            throw_if(! $response->ok(), RuntimeException::class, 'Catalog feed returned an unexpected status code.');

            $payload = $response->json();
            $plugins = $this->validatePayload($payload);

            Cache::put($cacheKey, $plugins, $cacheTtl);

            return [
                'plugins' => $this->mergePlugins($localPlugins, $plugins),
                'warning' => $warning,
            ];
        } catch (\Throwable $exception) {
            report($exception);

            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $cachedPlugins = $this->validatePayload([
                    'schema_version' => 1,
                    'plugins' => $cached,
                ], false);

                return [
                    'plugins' => $this->mergePlugins($localPlugins, $cachedPlugins),
                    'warning' => 'Hosted catalog is currently unavailable. Showing local and cached catalog data.',
                ];
            }

            return [
                'plugins' => $localPlugins,
                'warning' => 'Hosted catalog is currently unavailable. Showing local catalog data only.',
            ];
        }
    }

    private function cacheKey(string $url): string
    {
        return 'tp:plugin-catalog:feed:'.sha1($url);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadLocalPlugins(): array
    {
        $path = trim((string) config('tentapress.catalog.local_path', ''));
        if ($path === '') {
            return [];
        }

        $absolutePath = str_starts_with($path, '/') ? $path : base_path($path);
        if (! File::exists($absolutePath) || ! File::isFile($absolutePath)) {
            return [];
        }

        try {
            $raw = File::get($absolutePath);
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }

        return $this->validatePayload($payload);
    }

    /**
     * @param  array<int,array<string,mixed>>  $local
     * @param  array<int,array<string,mixed>>  $remote
     * @return array<int,array<string,mixed>>
     */
    private function mergePlugins(array $local, array $remote): array
    {
        $byId = [];

        foreach ($local as $entry) {
            $id = (string) ($entry['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $byId[$id] = $entry;
        }

        foreach ($remote as $entry) {
            $id = (string) ($entry['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $byId[$id] = array_merge($byId[$id] ?? [], $entry);
        }

        return array_values($byId);
    }

    private function assertUrl(string $url, bool $requireHttps): void
    {
        $parts = parse_url($url);

        throw_if(! is_array($parts), RuntimeException::class, 'Catalog feed URL is invalid.');

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''));

        throw_if($host === '', RuntimeException::class, 'Catalog feed URL must include a host.');

        if ($requireHttps) {
            throw_if($scheme !== 'https', RuntimeException::class, 'Catalog feed URL must use HTTPS.');
        } else {
            throw_if(! in_array($scheme, ['http', 'https'], true), RuntimeException::class, 'Catalog feed URL must use HTTP or HTTPS.');
        }
    }

    /**
     * @param  mixed  $payload
     * @return array<int,array<string,mixed>>
     */
    private function validatePayload(mixed $payload, bool $strictSchema = true): array
    {
        throw_if(! is_array($payload), RuntimeException::class, 'Catalog feed payload must be a JSON object.');

        if ($strictSchema) {
            $schemaVersion = (int) ($payload['schema_version'] ?? 0);
            throw_if($schemaVersion !== 1, RuntimeException::class, 'Catalog feed schema_version must be 1.');
        }

        $plugins = $payload['plugins'] ?? null;
        throw_if(! is_array($plugins), RuntimeException::class, 'Catalog feed must provide a plugins array.');

        $out = [];

        foreach ($plugins as $plugin) {
            if (! is_array($plugin)) {
                continue;
            }

            $id = strtolower(trim((string) ($plugin['id'] ?? '')));
            if (! preg_match('/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/', $id)) {
                continue;
            }

            if (! str_starts_with($id, 'tentapress/')) {
                continue;
            }

            $out[] = [
                'id' => $id,
                'name' => trim((string) ($plugin['name'] ?? $id)),
                'description' => trim((string) ($plugin['description'] ?? '')),
                'package' => strtolower(trim((string) ($plugin['package'] ?? $id))),
                'docs_url' => $this->normalizeOptionalUrl($plugin['docs_url'] ?? null),
                'repo_url' => $this->normalizeOptionalUrl($plugin['repo_url'] ?? null),
                'icon' => $this->normalizeIcon($plugin['icon'] ?? null),
                'latest_version' => trim((string) ($plugin['latest_version'] ?? '')),
                'tags' => $this->normalizeTags($plugin['tags'] ?? null),
            ];
        }

        return $out;
    }

    private function normalizeOptionalUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);
        if ($url === '') {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    private function normalizeIcon(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $icon = trim($value);
        if ($icon === '') {
            return null;
        }

        return mb_substr($icon, 0, 32);
    }

    /**
     * @param  mixed  $value
     * @return array<int,string>
     */
    private function normalizeTags(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $tags = array_values(array_filter(array_map(static fn (mixed $tag): string => trim((string) $tag), $value), static fn (string $tag): bool => $tag !== ''));

        return array_values(array_unique($tags));
    }
}
