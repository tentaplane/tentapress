<?php

declare(strict_types=1);

namespace TentaPress\Blocks\Render;

use Illuminate\Contracts\View\Factory as ViewFactory;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\System\Theme\ThemeManager;

final readonly class BlockRenderer
{
    private const MAX_NESTED_DEPTH = 1;

    public function __construct(
        private ViewFactory $views,
        private BlockRegistry $registry,
        private ThemeManager $themes,
    ) {
    }

    /**
     * @param  array{type?:mixed,version?:mixed,props?:mixed}  $block
     */
    public function render(array $block): string
    {
        return $this->renderWithDepth($block, 0);
    }

    /**
     * @param  array{type?:mixed,version?:mixed,props?:mixed}  $block
     */
    private function renderWithDepth(array $block, int $depth): string
    {
        $type = isset($block['type']) ? (string) $block['type'] : '';
        $props = isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
        $variant = isset($block['variant']) ? trim((string) $block['variant']) : '';

        if ($type === '') {
            return '';
        }

        $def = $this->registry->get($type);

        if ($variant === '' && $def && is_string($def->defaultVariant)) {
            $variant = $def->defaultVariant;
        }

        // Determine view key (dot notation)
        $viewKey = $def?->view;
        if (! $viewKey) {
            // Default: blocks/<name> => blocks.name
            $viewKey = 'blocks.'.$this->typeSlug($type);
        }

        $viewKey = $this->resolveVariantView($viewKey, $variant, $def?->variants ?? []);
        $renderBlocks = function (array $children) use ($depth): string {
            if ($depth >= self::MAX_NESTED_DEPTH) {
                return '';
            }

            $html = '';

            foreach ($children as $child) {
                if (! is_array($child)) {
                    continue;
                }

                $childType = trim((string) ($child['type'] ?? ''));
                if ($childType === 'blocks/split-layout') {
                    continue;
                }

                $html .= $this->renderWithDepth($child, $depth + 1);
            }

            return $html;
        };
        $viewData = [
            'block' => $block,
            'props' => $props,
            'type' => $type,
            'variant' => $variant,
            'renderBlocks' => $renderBlocks,
            'depth' => $depth,
        ];

        // Theme override first
        if ($this->themes->hasActiveTheme() && $this->views->exists('tp-theme::'.$viewKey)) {
            return $this->views->make('tp-theme::'.$viewKey, $viewData)->render();
        }

        // Plugin fallback
        if ($this->views->exists('tentapress-blocks::'.$viewKey)) {
            return $this->views->make('tentapress-blocks::'.$viewKey, $viewData)->render();
        }

        // No view found
        return '';
    }

    private function typeSlug(string $type): string
    {
        // blocks/hero => hero, vendor/block => block
        $parts = explode('/', str_replace('\\', '/', $type));

        return (string) end($parts);
    }

    /**
     * @param  array<int,array<string,mixed>>  $variants
     */
    private function resolveVariantView(string $baseView, string $variant, array $variants): string
    {
        if ($variant === '') {
            return $baseView;
        }

        foreach ($variants as $entry) {
            $key = isset($entry['key']) ? (string) $entry['key'] : '';

            if ($key !== '' && $key === $variant && isset($entry['view'])) {
                $view = (string) $entry['view'];

                if ($view !== '') {
                    return $view;
                }
            }
        }

        $variantKey = str_replace(['\\', '/'], '.', $variant);

        return $baseView.'.'.$variantKey;
    }
}
