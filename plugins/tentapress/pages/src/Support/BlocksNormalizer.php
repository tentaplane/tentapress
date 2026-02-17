<?php

declare(strict_types=1);

namespace TentaPress\Pages\Support;

use TentaPress\Blocks\Registry\BlockRegistry;

final readonly class BlocksNormalizer
{
    private const MAX_NESTED_DEPTH = 1;

    private const NESTED_BLOCK_PROP_KEYS = [
        'left_blocks',
        'right_blocks',
    ];

    public function __construct(
        private BlockRegistry $registry,
    ) {
    }

    /**
     * @return array<int,array{type:string,version:int,props:array<string,mixed>,variant?:string}>
     */
    public function normalize(mixed $raw): array
    {
        return $this->normalizeBlocks($raw, 0);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function parseActions(string $raw): array
    {
        $trim = trim($raw);
        if ($trim === '') {
            return [];
        }

        $decoded = json_decode($trim, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, is_array(...)));
        }

        $lines = preg_split('/\r?\n/', $trim) ?: [];
        $actions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map(trim(...), explode('|', $line));
            $actions[] = [
                'label' => $parts[0] ?? $line,
                'url' => $parts[1] ?? '',
                'style' => $parts[2] ?? 'primary',
            ];
        }

        return $actions;
    }

    /**
     * Shallow defaults merge; nested objects are kept as-is unless missing.
     *
     * @param  array<string,mixed>  $defaults
     * @param  array<string,mixed>  $props
     * @return array<string,mixed>
     */
    private function mergeDefaults(array $defaults, array $props): array
    {
        foreach ($defaults as $k => $v) {
            if (! array_key_exists($k, $props)) {
                $props[$k] = $v;
            }
        }

        return $props;
    }

    /**
     * @return array<int,array{type:string,version:int,props:array<string,mixed>,variant?:string}>
     */
    private function normalizeBlocks(mixed $raw, int $depth): array
    {
        if (! is_array($raw)) {
            return [];
        }

        if (array_key_exists('blocks', $raw) && is_array($raw['blocks'])) {
            $raw = $raw['blocks'];
        }

        $out = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $normalized = $this->normalizeBlock($entry, $depth);

            if ($normalized !== null) {
                $out[] = $normalized;
            }
        }

        return $out;
    }

    /**
     * @param  array<string,mixed>  $block
     * @return array{type:string,version:int,props:array<string,mixed>,variant?:string}|null
     */
    private function normalizeBlock(array $block, int $depth): ?array
    {
        $type = isset($block['type']) ? trim((string) $block['type']) : '';
        if ($type === '') {
            return null;
        }

        if ($depth >= self::MAX_NESTED_DEPTH && $type === 'blocks/split-layout') {
            return null;
        }

        $props = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
        $variant = isset($block['variant']) ? trim((string) $block['variant']) : '';

        unset($props['_collapsed'], $props['_key']);

        if (array_key_exists('actions', $props) && is_string($props['actions'])) {
            $props['actions'] = $this->parseActions($props['actions']);
        }

        $def = $this->registry->get($type);

        $version = 1;
        if (isset($block['version']) && is_numeric($block['version'])) {
            $version = (int) $block['version'];
        } elseif ($def) {
            $version = (int) $def->version;
        }

        if ($def && is_array($def->defaults) && $def->defaults !== []) {
            $props = $this->mergeDefaults($def->defaults, $props);
        }

        if ($variant === '' && $def && is_string($def->defaultVariant)) {
            $variant = $def->defaultVariant;
        }

        foreach (self::NESTED_BLOCK_PROP_KEYS as $nestedKey) {
            if (! array_key_exists($nestedKey, $props)) {
                continue;
            }

            $props[$nestedKey] = $depth < self::MAX_NESTED_DEPTH
                ? $this->normalizeBlocks($props[$nestedKey], $depth + 1)
                : [];
        }

        return [
            'type' => $type,
            'version' => $version,
            'props' => $props,
            ...($variant !== '' ? ['variant' => $variant] : []),
        ];
    }
}
