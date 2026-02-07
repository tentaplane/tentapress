<?php

declare(strict_types=1);

namespace TentaPress\Themes\Http\Admin;

use Illuminate\Support\Facades\File;
use TentaPress\System\Support\Paths;
use TentaPress\System\Theme\ThemeManager;
use TentaPress\System\Theme\ThemeRegistry;

final class IndexController
{
    public function __invoke(ThemeRegistry $registry, ThemeManager $manager)
    {
        $themes = $registry->listAll();
        $active = $manager->activeTheme();
        $activeId = $active['id'] ?? null;

        $themes = array_map(function (array $t) use ($activeId): array {
            $manifest = $this->decodeManifest($t['manifest'] ?? null);

            $t['layouts'] = $this->extractLayouts($manifest);
            $t['has_screenshot'] = $this->hasScreenshot((string) ($t['path'] ?? ''));

            // convenience flags
            $id = (string) ($t['id'] ?? '');
            $t['is_active'] = ($activeId !== null && $activeId === $id);

            return $t;
        }, $themes);

        usort($themes, function (array $left, array $right): int {
            $leftActive = (bool) ($left['is_active'] ?? false);
            $rightActive = (bool) ($right['is_active'] ?? false);

            if ($leftActive !== $rightActive) {
                return $leftActive ? -1 : 1;
            }

            $leftName = strtolower((string) ($left['name'] ?? $left['id'] ?? ''));
            $rightName = strtolower((string) ($right['name'] ?? $right['id'] ?? ''));

            return $leftName <=> $rightName;
        });

        return view('tentapress-themes::index', [
            'themes' => $themes,
            'activeId' => $activeId,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeManifest(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string,mixed>  $manifest
     * @return array<int,array{key:string,label:string}>
     */
    private function extractLayouts(array $manifest): array
    {
        $layouts = $manifest['layouts'] ?? [];

        if (! is_array($layouts)) {
            return [];
        }

        $out = [];

        foreach ($layouts as $entry) {
            if (is_string($entry)) {
                $key = trim($entry);

                if ($key === '') {
                    continue;
                }

                $out[] = ['key' => $key, 'label' => ucfirst($key)];

                continue;
            }

            if (! is_array($entry)) {
                continue;
            }

            $key = isset($entry['key']) ? trim((string) $entry['key']) : '';

            if ($key === '') {
                continue;
            }

            $label = isset($entry['label']) ? trim((string) $entry['label']) : '';

            if ($label === '') {
                $label = ucfirst($key);
            }

            $out[] = ['key' => $key, 'label' => $label];
        }

        $deduped = [];

        foreach ($out as $l) {
            $k = (string) ($l['key'] ?? '');

            if ($k === '' || isset($deduped[$k])) {
                continue;
            }

            $deduped[$k] = $l;
        }

        return array_values($deduped);
    }

    private function hasScreenshot(string $themeRelativePath): bool
    {
        $themeRelativePath = trim($themeRelativePath);

        if ($themeRelativePath === '') {
            return false;
        }

        foreach (['screenshot.png', 'screenshot.jpg', 'screenshot.jpeg', 'screenshot.webp'] as $file) {
            $full = Paths::themesPath($themeRelativePath.'/'.$file);

            if (File::exists($full) && File::isFile($full)) {
                return true;
            }
        }

        return false;
    }
}
