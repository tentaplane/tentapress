<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

final class PluginAssetPublisher
{
    public function publish(string $pluginId, string $pluginPath): void
    {
        $parts = array_values(array_filter(explode('/', $pluginId)));
        if (count($parts) !== 2) {
            return;
        }

        [$vendor, $name] = $parts;

        $source = $this->resolveSourcePath($pluginPath, $vendor, $name);
        if ($source === null || ! is_dir($source)) {
            return;
        }

        $destination = public_path('plugins/'.$vendor.'/'.$name.'/build');
        $this->copyDirectory($source, $destination);
    }

    private function resolveSourcePath(string $pluginPath, string $vendor, string $name): ?string
    {
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

    private function copyDirectory(string $source, string $destination): void
    {
        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
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
}
