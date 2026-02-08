<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Throwable;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\SystemInfo\Models\TpPluginInstall;

final class IndexController
{
    /**
     * @return View
     */
    public function __invoke(PluginRegistry $registry): View
    {
        $error = null;
        $plugins = [];
        $installAttempts = [];
        $installTableExists = false;

        try {
            $installTableExists = Schema::hasTable('tp_plugin_installs');

            if (! Schema::hasTable('tp_plugins')) {
                $error = 'Plugin table not found. Run migrations to manage plugins.';
            } else {
                $plugins = $this->buildPlugins($registry->listAll(), $registry);
            }

            if ($installTableExists) {
                $installAttempts = TpPluginInstall::query()
                    ->latest('id')
                    ->limit(12)
                    ->get()
                    ->map(fn (TpPluginInstall $attempt): array => [
                        'id' => (int) $attempt->id,
                        'package' => (string) $attempt->package,
                        'status' => (string) $attempt->status,
                        'requested_by' => $attempt->requested_by !== null ? (int) $attempt->requested_by : null,
                        'output' => (string) ($attempt->output ?? ''),
                        'error' => (string) ($attempt->error ?? ''),
                        'created_at' => $attempt->created_at?->toIso8601String(),
                        'started_at' => $attempt->started_at?->toIso8601String(),
                        'finished_at' => $attempt->finished_at?->toIso8601String(),
                    ])
                    ->all();
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        return view('tentapress-system-info::plugins.index', [
            'plugins' => $plugins,
            'error' => $error,
            'installTableExists' => $installTableExists,
            'installAttempts' => $installAttempts,
            'canInstallPlugins' => $this->canInstallPlugins(),
        ]);
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function buildPlugins(array $rows, PluginRegistry $registry): array
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
            $path = trim((string) ($data['path'] ?? ''));
            $installed = $registry->isPluginInstalled($data);
            $protected = in_array($id, PluginRegistry::PROTECTED_PLUGIN_IDS, true);

            $plugins[] = [
                'id' => $id,
                'name' => $name !== '' ? $name : $id,
                'description' => $description,
                'version' => $version,
                'provider' => $provider,
                'path' => $path,
                'enabled' => $enabled,
                'installed' => $installed,
                'protected' => $protected,
            ];
        }

        usort($plugins, static function (array $a, array $b): int {
            $nameCompare = strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            if ($nameCompare !== 0) {
                return $nameCompare;
            }

            return strcasecmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
        });

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

    private function canInstallPlugins(): bool
    {
        $user = auth()->user();

        return is_object($user) && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }
}
