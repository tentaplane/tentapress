<?php

declare(strict_types=1);

namespace TentaPress\Blocks\Render;

use Illuminate\Contracts\View\Factory as ViewFactory;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\System\Theme\ThemeManager;

final readonly class BlockRenderer
{
    private const MAX_NESTED_DEPTH = 1;

    private const SPACING_MAP = [
        'none' => '0',
        'xs' => '0.5rem',
        'sm' => '1rem',
        'md' => '1.5rem',
        'lg' => '2rem',
        'xl' => '3rem',
    ];

    private const BACKGROUND_MAP = [
        'muted' => '#f8fafc',
        'brand' => '#e0ecf8',
    ];

    private const CONTAINER_MAP = [
        'default' => '80rem',
        'wide' => '96rem',
        'full' => 'none',
    ];

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
            $html = $this->views->make('tp-theme::'.$viewKey, $viewData)->render();

            return $this->wrapPresentation($html, $props);
        }

        // Plugin fallback
        if ($this->views->exists('tentapress-blocks::'.$viewKey)) {
            $html = $this->views->make('tentapress-blocks::'.$viewKey, $viewData)->render();

            return $this->wrapPresentation($html, $props);
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

    /**
     * @param  array<string,mixed>  $props
     */
    private function wrapPresentation(string $html, array $props): string
    {
        $presentation = $props['presentation'] ?? null;
        if (! is_array($presentation)) {
            return $html;
        }

        $styleParts = [];
        $classes = ['tp-block-presentation'];

        $spacing = $presentation['spacing'] ?? null;
        if (is_array($spacing)) {
            $top = trim((string) ($spacing['top'] ?? ''));
            if (isset(self::SPACING_MAP[$top])) {
                $styleParts[] = 'margin-top:'.self::SPACING_MAP[$top];
            }

            $bottom = trim((string) ($spacing['bottom'] ?? ''));
            if (isset(self::SPACING_MAP[$bottom])) {
                $styleParts[] = 'margin-bottom:'.self::SPACING_MAP[$bottom];
            }
        }

        $align = trim((string) ($presentation['align'] ?? ''));
        if (in_array($align, ['left', 'center', 'right'], true)) {
            $styleParts[] = 'text-align:'.$align;
        }

        $background = trim((string) ($presentation['background'] ?? ''));
        if (isset(self::BACKGROUND_MAP[$background])) {
            $styleParts[] = 'background-color:'.self::BACKGROUND_MAP[$background];
            $styleParts[] = 'padding:1.25rem';
            $styleParts[] = 'border-radius:0.75rem';
        }

        $container = trim((string) ($presentation['container'] ?? ''));
        if (isset(self::CONTAINER_MAP[$container]) && self::CONTAINER_MAP[$container] !== 'none') {
            $styleParts[] = 'max-width:'.self::CONTAINER_MAP[$container];
            $styleParts[] = 'margin-left:auto';
            $styleParts[] = 'margin-right:auto';
        }

        if ($styleParts === []) {
            return $html;
        }

        $style = implode(';', $styleParts);

        return '<div class="'.e(implode(' ', $classes)).'" style="'.e($style).'">'.$html.'</div>';
    }
}
