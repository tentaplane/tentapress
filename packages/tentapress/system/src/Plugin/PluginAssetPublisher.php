<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

final class PluginAssetPublisher
{
    /**
     * Split a plugin ID into [vendor, name], or null if malformed.
     *
     * @return array{0:string,1:string}|null
     */
    public static function splitPluginId(string $pluginId): ?array
    {
        $parts = array_values(array_filter(explode('/', $pluginId)));

        return count($parts) === 2 ? [$parts[0], $parts[1]] : null;
    }

    public function publish(string $pluginId, string $pluginPath): void
    {
        $split = self::splitPluginId($pluginId);
        if ($split === null) {
            return;
        }

        [$vendor, $name] = $split;

        $source = $this->resolveSourcePath($pluginPath, $vendor, $name);
        if ($source === null || ! is_dir($source)) {
            return;
        }

        $destination = public_path('plugins/'.$vendor.'/'.$name.'/build');
        $this->copyDirectory($source, $destination);
    }

    public function unpublish(string $pluginId): void
    {
        $split = self::splitPluginId($pluginId);
        if ($split === null) {
            return;
        }

        [$vendor, $name] = $split;
        $destination = public_path('plugins/'.$vendor.'/'.$name.'/build');
        $this->deleteDirectory($destination);
    }

    private function resolveSourcePath(string $pluginPath, string $vendor, string $name): ?string
    {
        $pluginPath = $this->normalizePath($pluginPath);

        $candidates = [
            rtrim($pluginPath, '/').'/build',
            rtrim($pluginPath, '/').'/public/plugins/'.$vendor.'/'.$name.'/build',
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = rtrim(str_replace('\\', '/', $path), '/');
        if ($trimmed === '') {
            return $trimmed;
        }

        if ($this->isAbsolutePath($trimmed)) {
            return $trimmed;
        }

        return str_replace('\\', '/', base_path($trimmed));
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || (bool) preg_match('/^[A-Za-z]:\//', $path);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        $this->deleteDirectory($destination);

        if (! is_dir($destination) && ! @mkdir($destination, 0755, true) && ! is_dir($destination)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
            if ($item->isDir()) {
                if (! is_dir($target)) {
                    @mkdir($target, 0755, true);
                }
                continue;
            }

            @copy($item->getPathname(), $target);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($path);
    }
}
