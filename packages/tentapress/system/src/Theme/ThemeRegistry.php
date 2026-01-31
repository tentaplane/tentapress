<?php

declare(strict_types=1);

namespace TentaPress\System\Theme;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Finder\Finder;
use TentaPress\System\Support\Paths;

final class ThemeRegistry
{
    /**
     * @return array<string,ThemeManifest> keyed by theme id
     */
    public function discoverManifests(): array
    {
        $paths = $this->themeSearchPaths();
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

            $manifest = ThemeManifest::fromFile($manifestPath);

            // Only accept manifests that declare type/theme OR have no type.
            $type = $manifest->data['type'] ?? null;
            $isVendor = str_starts_with($manifestPath, base_path('vendor').DIRECTORY_SEPARATOR);

            if ($isVendor) {
                if ((string) $type !== 'theme') {
                    continue;
                }
            } elseif ($type !== null && (string) $type !== 'theme') {
                continue;
            }

            $manifests[$manifest->id] = $manifest;
        }

        ksort($manifests);

        return $manifests;
    }

    /**
     * Sync filesystem manifests into tp_themes.
     *
     * @return int number of records upserted
     */
    public function sync(): int
    {
        $themesDirExists = $this->themeSearchPaths() !== [];

        $manifests = $this->discoverManifests();
        $themeIds = array_keys($manifests);

        $upserted = 0;

        if ($manifests !== []) {
            $now = now();

            $rows = [];
            foreach ($manifests as $m) {
                $rows[] = [
                    'id' => $m->id,
                    'name' => $m->name,
                    'version' => $m->version,
                    'path' => $m->path,
                    'manifest' => json_encode($m->data, JSON_THROW_ON_ERROR),
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }

            DB::table('tp_themes')->upsert(
                $rows,
                ['id'],
                ['name', 'version', 'path', 'manifest', 'updated_at']
            );

            $upserted = count($rows);
        }

        if ($themesDirExists) {
            if ($themeIds === []) {
                DB::table('tp_themes')->delete();
            } else {
                DB::table('tp_themes')->whereNotIn('id', $themeIds)->delete();
            }

            $activeId = $this->activeThemeIdFromSettings();
            if ($activeId !== null && ! in_array($activeId, $themeIds, true)) {
                $this->clearActiveThemeSetting();
                $this->clearThemeCache();
            }
        }

        return $upserted;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listAll(): array
    {
        $rows = DB::table('tp_themes')
            ->orderBy('id')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        return $rows;
    }

    private function activeThemeIdFromSettings(): ?string
    {
        try {
            $row = DB::table('tp_settings')->where('key', 'active_theme')->first();

            if (! $row) {
                return null;
            }

            $value = $row->value ?? null;

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_string($decoded) && $decoded !== '') {
                    return $decoded;
                }

                if (is_array($decoded) && isset($decoded['id']) && is_string($decoded['id'])) {
                    return $decoded['id'];
                }
            }

            if (is_array($value) && isset($value['id']) && is_string($value['id'])) {
                return $value['id'];
            }

            return is_scalar($value) ? (string) $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function clearActiveThemeSetting(): void
    {
        DB::table('tp_settings')->where('key', 'active_theme')->delete();
    }

    private function clearThemeCache(): void
    {
        $path = Paths::themeCachePath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * @return array<int,string>
     */
    private function themeSearchPaths(): array
    {
        return Paths::themeSearchRoots();
    }
}
