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

            $activeId = ThemeManager::activeThemeIdFromSettings();
            if ($activeId !== null && ! in_array($activeId, $themeIds, true)) {
                $this->clearActiveThemeSetting();
                app(ThemeManager::class)->clearCache();
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

    private function clearActiveThemeSetting(): void
    {
        DB::table('tp_settings')->where('key', 'active_theme')->delete();
    }

    /**
     * @return array<int,string>
     */
    private function themeSearchPaths(): array
    {
        return Paths::themeSearchRoots();
    }
}
