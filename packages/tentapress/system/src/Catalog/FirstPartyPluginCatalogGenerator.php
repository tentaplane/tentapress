<?php

declare(strict_types=1);

namespace TentaPress\System\Catalog;

use Illuminate\Support\Facades\File;
use TentaPress\System\Support\JsonPayload;
use TentaPress\System\Support\Paths;

final readonly class FirstPartyPluginCatalogGenerator
{
    private const DEFAULT_CATALOG_PATH = 'docs/catalog/first-party-plugins.json';

    public function __construct(
        private JsonPayload $jsonPayload,
    ) {
    }

    /**
     * @return array{schema_version:int,generated_at:string,plugins:array<int,array<string,mixed>>}
     */
    public function generate(?string $path = null): array
    {
        $catalogPath = $this->resolveCatalogPath($path);
        $existingEntries = $this->existingEntriesById($catalogPath);
        $plugins = [];

        foreach ($this->manifestPaths() as $manifestPath) {
            $manifest = $this->decodeJsonFile($manifestPath);
            if ($manifest === []) {
                continue;
            }

            $id = strtolower(trim((string) ($manifest['id'] ?? '')));
            if ($id === '' || ! str_starts_with($id, 'tentapress/')) {
                continue;
            }

            $name = trim((string) ($manifest['name'] ?? $id));
            $description = trim((string) ($manifest['description'] ?? ''));
            $version = trim((string) ($manifest['version'] ?? ''));

            if ($version === '') {
                continue;
            }

            $composer = $this->decodeJsonFile(dirname($manifestPath).'/composer.json');
            $existing = $existingEntries[$id] ?? [];
            $icon = $this->resolveIcon($manifest, $existing);
            $tags = $this->normalizeTags($existing['tags'] ?? []);

            $entry = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'package' => $this->resolvePackageName($id, $composer, $existing),
                'latest_version' => $version,
            ];

            if ($icon !== null) {
                $entry['icon'] = $icon;
            }

            $docsUrl = $this->normalizeOptionalUrl($existing['docs_url'] ?? null);
            if ($docsUrl !== null) {
                $entry['docs_url'] = $docsUrl;
            }

            $repoUrl = $this->normalizeOptionalUrl($existing['repo_url'] ?? null);
            if ($repoUrl !== null) {
                $entry['repo_url'] = $repoUrl;
            }

            if ($tags !== []) {
                $entry['tags'] = $tags;
            }

            $plugins[] = $entry;
        }

        usort($plugins, static fn (array $left, array $right): int => strcmp((string) ($left['id'] ?? ''), (string) ($right['id'] ?? '')));

        return [
            'schema_version' => 1,
            'generated_at' => now()->utc()->format('Y-m-d\TH:i:s\Z'),
            'plugins' => $plugins,
        ];
    }

    public function write(?string $path = null): string
    {
        $catalogPath = $this->resolveCatalogPath($path);
        File::ensureDirectoryExists(dirname($catalogPath));
        File::put($catalogPath, $this->jsonPayload->encode($this->generate($catalogPath)));

        return $catalogPath;
    }

    public function isCurrent(?string $path = null): bool
    {
        $catalogPath = $this->resolveCatalogPath($path);
        if (! File::exists($catalogPath) || ! File::isFile($catalogPath)) {
            return false;
        }

        $current = $this->jsonPayload->decodeOrEmpty((string) File::get($catalogPath));
        $generated = $this->generate($catalogPath);

        return $this->normalizedPayload($current) === $this->normalizedPayload($generated);
    }

    /**
     * @return array<int,string>
     */
    private function manifestPaths(): array
    {
        $paths = glob(Paths::pluginsPath('tentapress/*/tentapress.json')) ?: [];

        sort($paths);

        return array_values(array_filter($paths, is_string(...)));
    }

    private function resolveCatalogPath(?string $path = null): string
    {
        $candidate = trim((string) ($path ?? ''));

        if ($candidate === '') {
            return base_path(self::DEFAULT_CATALOG_PATH);
        }

        return str_starts_with($candidate, '/') ? $candidate : base_path($candidate);
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function existingEntriesById(string $catalogPath): array
    {
        if (! File::exists($catalogPath) || ! File::isFile($catalogPath)) {
            return [];
        }

        $payload = $this->jsonPayload->decodeOrEmpty((string) File::get($catalogPath));
        $plugins = $payload['plugins'] ?? null;
        if (! is_array($plugins)) {
            return [];
        }

        $entries = [];

        foreach ($plugins as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $id = strtolower(trim((string) ($entry['id'] ?? '')));
            if ($id === '') {
                continue;
            }

            $entries[$id] = $entry;
        }

        return $entries;
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJsonFile(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        return $this->jsonPayload->decodeOrEmpty((string) file_get_contents($path));
    }

    /**
     * @param  array<string,mixed>  $manifest
     * @param  array<string,mixed>  $existing
     */
    private function resolveIcon(array $manifest, array $existing): ?string
    {
        $existingIcon = $this->normalizeIcon($existing['icon'] ?? null);
        if ($existingIcon !== null) {
            return $existingIcon;
        }

        $menus = $manifest['admin']['menus'] ?? null;
        if (! is_array($menus)) {
            return null;
        }

        foreach ($menus as $menu) {
            if (! is_array($menu)) {
                continue;
            }

            $icon = $this->normalizeIcon($menu['icon'] ?? null);
            if ($icon !== null) {
                return $icon;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $composer
     * @param  array<string,mixed>  $existing
     */
    private function resolvePackageName(string $id, array $composer, array $existing): string
    {
        $composerName = strtolower(trim((string) ($composer['name'] ?? '')));
        if ($composerName !== '') {
            return $composerName;
        }

        $existingPackage = strtolower(trim((string) ($existing['package'] ?? '')));
        if ($existingPackage !== '') {
            return $existingPackage;
        }

        return $id;
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    private function normalizedPayload(array $payload): array
    {
        unset($payload['generated_at']);

        return $payload;
    }

    private function normalizeIcon(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $icon = trim($value);

        return $icon === '' ? null : mb_substr($icon, 0, 32);
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

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
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

        $tags = array_map(static fn (mixed $tag): string => trim((string) $tag), $value);
        $tags = array_values(array_filter($tags, static fn (string $tag): bool => $tag !== ''));

        return array_values(array_unique($tags));
    }
}
