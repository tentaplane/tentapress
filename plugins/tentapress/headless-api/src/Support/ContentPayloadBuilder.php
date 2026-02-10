<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Support;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;

final class ContentPayloadBuilder
{
    /**
     * @return array{content_raw:array<string,mixed>,content_html:string}
     */
    public function forPage(TpPage $page): array
    {
        $editorDriver = (string) ($page->editor_driver ?? 'blocks');
        $blocks = $this->normalizeBlocks($page->blocks);
        $content = is_array($page->content) ? $page->content : null;

        $contentRaw = [
            'editor_driver' => $editorDriver,
            'blocks' => $blocks,
            'content' => $content,
        ];

        return [
            'content_raw' => $contentRaw,
            'content_html' => $this->renderHtml($editorDriver, $blocks, $content),
        ];
    }

    /**
     * @return array{content_raw:array<string,mixed>,content_html:string}
     */
    public function forPost(TpPost $post): array
    {
        $editorDriver = (string) ($post->editor_driver ?? 'blocks');
        $blocks = $this->normalizeBlocks($post->blocks);
        $content = is_array($post->content) ? $post->content : null;

        $contentRaw = [
            'editor_driver' => $editorDriver,
            'blocks' => $blocks,
            'content' => $content,
        ];

        return [
            'content_raw' => $contentRaw,
            'content_html' => $this->renderHtml($editorDriver, $blocks, $content),
        ];
    }

    /**
     * @param array<int,mixed> $blocks
     * @param array<string,mixed>|null $content
     */
    private function renderHtml(string $editorDriver, array $blocks, ?array $content): string
    {
        if ($editorDriver !== 'blocks' && is_array($content) && app()->bound('tp.page_editor.render')) {
            $renderer = resolve('tp.page_editor.render');
            if (is_callable($renderer)) {
                $html = $renderer($content);
                if (is_string($html) && trim($html) !== '') {
                    return $html;
                }
            }
        }

        if (app()->bound('tp.blocks.render')) {
            $renderer = resolve('tp.blocks.render');
            if (is_callable($renderer)) {
                $html = $renderer($blocks);
                if (is_string($html)) {
                    return $html;
                }
            }
        }

        return '';
    }

    /**
     * @return array<int,mixed>
     */
    private function normalizeBlocks(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        if (array_key_exists('blocks', $raw) && is_array($raw['blocks'])) {
            return array_values($raw['blocks']);
        }

        return array_values($raw);
    }
}
