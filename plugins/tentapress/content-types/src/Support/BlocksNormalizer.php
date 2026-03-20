<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Support;

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

        $definition = $this->registry->get($type);
        $version = isset($block['version']) && is_numeric($block['version']) ? (int) $block['version'] : (int) ($definition?->version ?? 1);

        if ($definition && is_array($definition->defaults) && $definition->defaults !== []) {
            foreach ($definition->defaults as $key => $defaultValue) {
                if (! array_key_exists($key, $props)) {
                    $props[$key] = $defaultValue;
                }
            }
        }

        if ($variant === '' && $definition && is_string($definition->defaultVariant)) {
            $variant = $definition->defaultVariant;
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
