<?php

declare(strict_types=1);

namespace TentaPress\CustomBlocks\Discovery;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\System\Support\Paths;
use TentaPress\System\Theme\ThemeManager;

final readonly class ThemeSingleFileBlockKit
{
    private const TYPE_PREFIX = 'tentapress/custom-blocks/';

    public function __construct(
        private ThemeManager $themes,
    ) {
    }

    public function register(BlockRegistry $registry): void
    {
        $active = $this->themes->activeTheme();

        if (! is_array($active)) {
            return;
        }

        $themePath = trim((string) ($active['path'] ?? ''));

        if ($themePath === '') {
            return;
        }

        $blocksPath = Paths::themesPath($themePath.'/views/blocks');

        if (! is_dir($blocksPath)) {
            return;
        }

        foreach ($this->blockFiles($blocksPath) as $filePath) {
            $definition = $this->definitionFromFile($filePath, $blocksPath);

            if (! $definition) {
                continue;
            }

            $registry->register($definition);
        }
    }

    /**
     * @return array<int,string>
     */
    private function blockFiles(string $blocksPath): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($blocksPath));
        $files = [];

        foreach ($iterator as $item) {
            if (! $item->isFile()) {
                continue;
            }

            $path = $item->getPathname();

            if (! str_ends_with($path, '.blade.php')) {
                continue;
            }

            $files[] = $path;
        }

        sort($files);

        return $files;
    }

    private function definitionFromFile(string $path, string $blocksPath): ?BlockDefinition
    {
        $raw = file_get_contents($path);

        if ($raw === false) {
            return null;
        }

        $meta = $this->extractMetadata($raw);

        if ($meta === null) {
            return null;
        }
        $relative = $this->relativeBlockPath($path, $blocksPath);

        if ($relative === '') {
            return null;
        }

        $view = $this->resolveView($relative, $meta);
        $type = $this->resolveType($relative, $meta);
        $name = $this->resolveName($relative, $meta);
        $description = $this->resolveDescription($meta);

        if ($type === '' || $name === '' || $description === '') {
            return null;
        }

        $fields = is_array($meta['fields'] ?? null) ? $meta['fields'] : [];
        $defaults = is_array($meta['defaults'] ?? null) ? $meta['defaults'] : [];
        $example = is_array($meta['example'] ?? null) ? $meta['example'] : [];
        $variants = is_array($meta['variants'] ?? null) ? $meta['variants'] : [];
        $version = isset($meta['version']) && is_numeric($meta['version']) ? (int) $meta['version'] : 1;
        $defaultVariant = isset($meta['default_variant']) ? trim((string) $meta['default_variant']) : null;

        return new BlockDefinition(
            type: $type,
            name: $name,
            description: $description,
            version: $version > 0 ? $version : 1,
            fields: $fields,
            defaults: $defaults,
            example: $example,
            view: $view,
            variants: $variants,
            defaultVariant: $defaultVariant !== '' ? $defaultVariant : null,
        );
    }

    /**
     * @return array<string,mixed>|null
     */
    private function extractMetadata(string $raw): ?array
    {
        if (! preg_match('/\{\{--\s*tp:block\b(.*?)--\}\}/s', $raw, $matches)) {
            return null;
        }

        $json = trim((string) ($matches[1] ?? ''));

        if ($json === '') {
            return null;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function relativeBlockPath(string $path, string $blocksPath): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', $blocksPath), '/').'/';

        if (! str_starts_with($normalizedPath, $normalizedRoot)) {
            return '';
        }

        $relative = substr($normalizedPath, strlen($normalizedRoot));

        if (! is_string($relative) || ! str_ends_with($relative, '.blade.php')) {
            return '';
        }

        return substr($relative, 0, -10);
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    private function resolveType(string $relative, array $meta): string
    {
        $type = trim((string) ($meta['type'] ?? ''));

        if ($type !== '') {
            return $type;
        }

        $slug = str_replace('/', '-', $relative);

        return self::TYPE_PREFIX.$slug;
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    private function resolveName(string $relative, array $meta): string
    {
        $name = trim((string) ($meta['name'] ?? ''));

        if ($name !== '') {
            return $name;
        }

        $base = basename($relative);
        $base = str_replace(['-', '_'], ' ', $base);

        return ucwords(trim($base));
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    private function resolveDescription(array $meta): string
    {
        $description = trim((string) ($meta['description'] ?? ''));

        return $description !== '' ? $description : 'Custom block from active theme.';
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    private function resolveView(string $relative, array $meta): string
    {
        $view = trim((string) ($meta['view'] ?? ''));

        if ($view !== '') {
            return $view;
        }

        return 'blocks.'.str_replace('/', '.', $relative);
    }
}
