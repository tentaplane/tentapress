<?php

declare(strict_types=1);

namespace TentaPress\Pages\Support;

use TentaPress\Blocks\Registry\BlockRegistry;

final readonly class BlocksNormalizer
{
    public function __construct(
        private BlockRegistry $registry,
    ) {
    }

    /**
     * @return array<int,array{type:string,version:int,props:array<string,mixed>,variant?:string}>
     */
    public function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];

        foreach ($raw as $b) {
            if (! is_array($b)) {
                continue;
            }

            $type = isset($b['type']) ? trim((string) $b['type']) : '';
            if ($type === '') {
                continue;
            }

            $props = isset($b['props']) && is_array($b['props']) ? $b['props'] : [];
            $variant = isset($b['variant']) ? trim((string) $b['variant']) : '';

            // Strip UI-only keys nested in props just in case
            unset($props['_collapsed'], $props['_key']);

            $def = $this->registry->get($type);

            $version = 1;
            if (isset($b['version']) && is_numeric($b['version'])) {
                $version = (int) $b['version'];
            } elseif ($def) {
                $version = (int) $def->version;
            }

            // Apply defaults for missing keys (shallow merge)
            if ($def && is_array($def->defaults) && $def->defaults !== []) {
                $props = $this->mergeDefaults($def->defaults, $props);
            }

            if ($variant === '' && $def && is_string($def->defaultVariant)) {
                $variant = $def->defaultVariant;
            }

            $out[] = [
                'type' => $type,
                'version' => $version,
                'props' => $props,
                ...($variant !== '' ? ['variant' => $variant] : []),
            ];
        }

        return $out;
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
}
