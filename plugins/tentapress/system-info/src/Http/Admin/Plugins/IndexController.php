<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Throwable;
use TentaPress\System\Plugin\PluginRegistry;

final class IndexController
{
    /**
     * @return View
     */
    public function __invoke(PluginRegistry $registry): View
    {
        $error = null;
        $plugins = [];

        try {
            if (! Schema::hasTable('tp_plugins')) {
                $error = 'Plugin table not found. Run migrations to manage plugins.';
            } else {
                $plugins = $this->buildPlugins($registry->listAll());
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('tentapress-system-info::plugins.index', [
            'plugins' => $plugins,
            'error' => $error,
        ]);
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function buildPlugins(array $rows): array
    {
        $plugins = [];

        foreach ($rows as $row) {
            $data = is_array($row) ? $row : (array) $row;
            $id = (string) ($data['id'] ?? '');
            $provider = trim((string) ($data['provider'] ?? ''));
            $manifest = $this->decodeManifest($data['manifest'] ?? null);

            $name = (string) ($manifest['name'] ?? $id);
            $description = (string) ($manifest['description'] ?? '');
            $version = (string) ($data['version'] ?? ($manifest['version'] ?? ''));
            $enabled = ((int) ($data['enabled'] ?? 0)) === 1;
            $installed = $provider !== '' && class_exists($provider);
            $protected = in_array($id, PluginRegistry::PROTECTED_PLUGIN_IDS, true);

            $plugins[] = [
                'id' => $id,
                'name' => $name !== '' ? $name : $id,
                'description' => $description,
                'version' => $version,
                'provider' => $provider,
                'path' => (string) ($data['path'] ?? ''),
                'enabled' => $enabled,
                'installed' => $installed,
                'protected' => $protected,
            ];
        }

        return $plugins;
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeManifest(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
