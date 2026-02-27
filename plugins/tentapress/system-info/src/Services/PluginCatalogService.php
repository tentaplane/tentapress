<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Services;

use Throwable;
use TentaPress\System\Plugin\PluginRegistry;

final readonly class PluginCatalogService
{
    public function __construct(
        private PluginRegistry $plugins,
        private CatalogFeedClient $feedClient,
    ) {
    }

    /**
     * @return array{entries:array<int,array<string,mixed>>,warning:?string}
     */
    public function catalog(): array
    {
        $feed = $this->feedClient->fetch();
        $hostedEntries = [];

        foreach ($feed['plugins'] as $entry) {
            $id = (string) ($entry['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $hostedEntries[$id] = $entry;
        }

        $localEntries = $this->localEntriesById();
        $ids = array_values(array_unique(array_merge(array_keys($hostedEntries), array_keys($localEntries))));

        $entries = [];

        foreach ($ids as $id) {
            $hosted = $hostedEntries[$id] ?? null;
            $local = $localEntries[$id] ?? null;

            $entries[] = [
                'id' => $id,
                'name' => $this->firstNonEmpty([
                    $hosted['name'] ?? null,
                    $local['name'] ?? null,
                    $id,
                ]),
                'description' => $this->firstNonEmpty([
                    $hosted['description'] ?? null,
                    $local['description'] ?? null,
                ]),
                'package' => $this->firstNonEmpty([
                    $hosted['package'] ?? null,
                    $id,
                ]),
                'docs_url' => $hosted['docs_url'] ?? null,
                'repo_url' => $hosted['repo_url'] ?? null,
                'tags' => is_array($hosted['tags'] ?? null) ? $hosted['tags'] : [],
                'latest_version' => $this->firstNonEmpty([
                    $hosted['latest_version'] ?? null,
                ]),
                'installed_version' => (string) ($local['installed_version'] ?? ''),
                'installed' => (bool) ($local['installed'] ?? false),
                'enabled' => (bool) ($local['enabled'] ?? false),
                'local_only' => $local !== null && $hosted === null,
            ];
        }

        usort($entries, static function (array $a, array $b): int {
            $nameCompare = strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            if ($nameCompare !== 0) {
                return $nameCompare;
            }

            return strcasecmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
        });

        return [
            'entries' => $entries,
            'warning' => $feed['warning'] ?? null,
        ];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function localEntriesById(): array
    {
        $rows = $this->plugins->listAll();
        $local = [];

        foreach ($rows as $row) {
            $id = strtolower(trim((string) ($row['id'] ?? '')));
            if ($id === '' || ! str_starts_with($id, 'tentapress/')) {
                continue;
            }

            $manifest = $this->decodeManifest($row['manifest'] ?? null);
            $installed = false;

            try {
                $installed = $this->plugins->isPluginInstalled($row);
            } catch (Throwable) {
                $installed = false;
            }

            $local[$id] = [
                'id' => $id,
                'name' => trim((string) ($manifest['name'] ?? $id)),
                'description' => trim((string) ($manifest['description'] ?? '')),
                'installed_version' => trim((string) ($row['version'] ?? ($manifest['version'] ?? ''))),
                'installed' => $installed,
                'enabled' => ((int) ($row['enabled'] ?? 0)) === 1,
            ];
        }

        return $local;
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

    /**
     * @param  array<int,mixed>  $values
     */
    private function firstNonEmpty(array $values): string
    {
        foreach ($values as $value) {
            $candidate = trim((string) $value);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }
}
