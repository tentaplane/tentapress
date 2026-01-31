<?php

declare(strict_types=1);

namespace TentaPress\Themes\Http\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use TentaPress\System\Support\Paths;
use TentaPress\System\Theme\ThemeManager;

final class ShowController
{
    public function __invoke(string $themePath, ThemeManager $manager)
    {
        $themeId = trim($themePath, '/');
        $row = DB::table('tp_themes')->where('id', $themeId)->first();

        abort_unless($row, 404);

        $active = $manager->activeTheme();
        $activeId = $active['id'] ?? null;

        $manifest = $this->decodeManifest($row->manifest ?? null);
        $layouts = $this->extractLayouts($manifest);

        $path = (string) ($row->path ?? '');
        $screenshotUrl = $this->resolveScreenshotUrl($themeId, $path);

        try {
            $prettyManifest = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $prettyManifest = '{}';
        }

        return view('tentapress-themes::show', [
            'theme' => [
                'id' => $themeId,
                'name' => (string) $row->name,
                'version' => (string) ($row->version ?? ''),
                'path' => $path,
                'description' => is_string($manifest['description'] ?? null) ? (string) $manifest['description'] : '',
                'layouts' => $layouts,
                'manifest_pretty' => $prettyManifest,
            ],
            'activeId' => $activeId,
            'screenshotUrl' => $screenshotUrl,
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

        // De-dupe by key
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

    private function resolveScreenshotUrl(string $themeId, string $themePath): ?string
    {
        $candidates = [
            'screenshot.png',
            'screenshot.jpg',
            'screenshot.jpeg',
            'screenshot.webp',
        ];

        foreach ($candidates as $file) {
            $full = Paths::themesPath($themePath.'/'.$file);

            if (File::exists($full)) {
                return route('tp.themes.screenshot', ['themePath' => $themeId]);
            }
        }

        return null;
    }
}
