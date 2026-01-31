<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class PluginManifest
{
    /**
     * @param  array<string,mixed>  $data
     */
    private function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $provider,
        public string $path,
        public array $data,
    ) {
    }

    public static function fromFile(string $manifestPath): self
    {
        throw_unless(is_file($manifestPath), RuntimeException::class, "Plugin manifest not found at: {$manifestPath}");

        $raw = file_get_contents($manifestPath);
        throw_if($raw === false, RuntimeException::class, "Unable to read plugin manifest at: {$manifestPath}");

        try {
            $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new RuntimeException("Invalid JSON in plugin manifest: {$manifestPath}. {$e->getMessage()}", 0, $e);
        }

        $id = (string) Arr::get($json, 'id', '');
        $name = (string) Arr::get($json, 'name', '');
        $version = (string) Arr::get($json, 'version', '0.0.0');
        $provider = (string) Arr::get($json, 'provider', '');

        throw_if($id === '' || ! Str::contains($id, '/'), RuntimeException::class, "Plugin manifest {$manifestPath} is missing a valid 'id' (expected vendor/name).");

        throw_if($name === '', RuntimeException::class, "Plugin manifest {$manifestPath} is missing required 'name'.");

        throw_if($provider === '', RuntimeException::class, "Plugin manifest {$manifestPath} is missing required 'provider'.");

        $pluginDir = dirname($manifestPath);
        $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $pluginDir);
        $relativePath = str_replace('\\', '/', $relativePath);

        return new self(
            id: $id,
            name: $name,
            version: $version,
            provider: $provider,
            path: $relativePath,
            data: $json,
        );
    }
}
