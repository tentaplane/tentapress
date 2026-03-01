<?php

declare(strict_types=1);

namespace TentaPress\Builder\Support;

use TentaPress\Blocks\Render\BlockRenderer;

final readonly class BuilderPreviewBlockRenderer
{
    public function __construct(
        private BlockRenderer $renderer,
    ) {
    }

    /**
     * @param  array<int,mixed>|array{blocks?:array<int,mixed>}  $rawBlocks
     * @return array{html:string,block_map:array<int,array{index:int,key:string}>}
     */
    public function render(array $rawBlocks): array
    {
        $blocks = $this->normalize($rawBlocks);
        $html = '';
        $blockMap = [];

        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $blockHtml = $this->renderer->render($block, [
                'builder_preview' => true,
            ]);
            if (trim($blockHtml) === '') {
                continue;
            }

            $key = $this->resolveKey($block, $index);
            $blockMap[] = [
                'index' => $index,
                'key' => $key,
            ];

            $html .= '<div class="tp-builder-preview-block" data-tp-builder-block-index="'.$index.'" data-tp-builder-block-key="'.$this->escapeAttr($key).'">'.$blockHtml.'</div>';
        }

        return [
            'html' => $html,
            'block_map' => $blockMap,
        ];
    }

    /**
     * @param  array<int,mixed>|array{blocks?:array<int,mixed>}  $rawBlocks
     * @return array<int,mixed>
     */
    private function normalize(array $rawBlocks): array
    {
        if (isset($rawBlocks['blocks']) && is_array($rawBlocks['blocks'])) {
            return array_values($rawBlocks['blocks']);
        }

        return array_values($rawBlocks);
    }

    /**
     * @param  array<string,mixed>  $block
     */
    private function resolveKey(array $block, int $index): string
    {
        $candidate = trim((string) ($block['_key'] ?? $block['key'] ?? ''));

        return $candidate !== '' ? $candidate : 'b_'.$index;
    }

    private function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
