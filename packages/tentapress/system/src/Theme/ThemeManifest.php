<?php

declare(strict_types=1);

namespace TentaPress\System\Theme;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use TentaPress\System\Support\Paths;

final readonly class ThemeManifest
{
    /**
     * @param  array<string,mixed>  $data
     */
    private function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $path, // relative to themes root, e.g. "tentapress/default"
        public array $data,
    ) {
    }

    public static function fromFile(string $manifestPath): self
    {
        throw_unless(is_file($manifestPath), RuntimeException::class, "Theme manifest not found at: {$manifestPath}");

        $raw = file_get_contents($manifestPath);
        throw_if($raw === false, RuntimeException::class, "Unable to read theme manifest at: {$manifestPath}");

        try {
            $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new RuntimeException("Invalid JSON in theme manifest: {$manifestPath}. {$e->getMessage()}", 0, $e);
        }

        $id = (string) Arr::get($json, 'id', '');
        $name = (string) Arr::get($json, 'name', '');
        $version = (string) Arr::get($json, 'version', '0.0.0');

        throw_if($id === '' || ! Str::contains($id, '/'), RuntimeException::class, "Theme manifest {$manifestPath} is missing a valid 'id' (expected vendor/name).");

        throw_if($name === '', RuntimeException::class, "Theme manifest {$manifestPath} is missing required 'name'.");

        $themeDir = dirname($manifestPath);

        // IMPORTANT: store path relative to the THEMES root (not the repo root)
        $themesRoot = Paths::themesPath();
        $prefix = rtrim($themesRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        $relativePath = str_starts_with($themeDir, $prefix)
            ? substr($themeDir, strlen($prefix))
            : $themeDir;

        $relativePath = str_replace('\\', '/', $relativePath);

        return new self(
            id: $id,
            name: $name,
            version: $version,
            path: $relativePath,
            data: $json,
        );
    }
}
