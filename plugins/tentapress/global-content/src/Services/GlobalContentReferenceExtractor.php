<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

final class GlobalContentReferenceExtractor
{
    /**
     * @param  array<int,mixed>|array{blocks?:array<int,mixed>}  $raw
     * @return array<int,int>
     */
    public function fromBlocks(array $raw): array
    {
        $blocks = $this->normalizeBlocks($raw);
        $references = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = trim((string) ($block['type'] ?? ''));
            $props = is_array($block['props'] ?? null) ? $block['props'] : [];

            if ($type === 'tentapress/global-content/reference') {
                $id = (int) ($props['global_content_id'] ?? 0);
                if ($id > 0) {
                    $references[$id] = $id;
                }
            }

            foreach (['left_blocks', 'right_blocks'] as $key) {
                if (is_array($props[$key] ?? null)) {
                    foreach ($this->fromBlocks($props[$key]) as $nestedId) {
                        $references[$nestedId] = $nestedId;
                    }
                }
            }
        }

        return array_values($references);
    }

    /**
     * @param  array<string,mixed>|null  $document
     * @return array<int,int>
     */
    public function fromPageDocument(?array $document): array
    {
        if (! is_array($document) || ! is_array($document['blocks'] ?? null)) {
            return [];
        }

        $references = [];

        foreach ($document['blocks'] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = trim((string) ($block['type'] ?? ''));
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if ($type !== 'globalContent') {
                continue;
            }

            $id = (int) ($data['global_content_id'] ?? 0);
            if ($id > 0) {
                $references[$id] = $id;
            }
        }

        return array_values($references);
    }

    /**
     * @param  array<int,mixed>|array{blocks?:array<int,mixed>}  $raw
     * @return array<int,mixed>
     */
    private function normalizeBlocks(array $raw): array
    {
        if (isset($raw['blocks']) && is_array($raw['blocks'])) {
            return array_values($raw['blocks']);
        }

        return array_values($raw);
    }
}
