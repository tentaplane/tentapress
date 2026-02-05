<?php

declare(strict_types=1);

namespace TentaPress\Pages\Services;

use Illuminate\Http\Response;
use TentaPress\Pages\Models\TpPage;
use TentaPress\System\Theme\ThemeManager;

final class PageRenderer
{
    public function render(TpPage $page): Response
    {
        $layoutKey = (string) ($page->layout ?: 'default');
        $blocks = $this->normalizeBlocks($page->blocks);
        $content = is_array($page->content) ? $page->content : null;
        $editorDriver = (string) ($page->editor_driver ?? '');
        $blocksHtml = $this->renderPageContent($content, $blocks, $editorDriver);
        $view = $this->resolveLayoutView($layoutKey);
        $isPageEditorContent = $editorDriver === 'page' && is_array($content);

        return response()->view($view, [
            'page' => $page,
            'layoutKey' => $layoutKey,
            'blocksHtml' => $blocksHtml,
            'isPageEditorContent' => $isPageEditorContent,
        ]);
    }

    /**
     * @param  array<int,mixed>  $blocks
     */
    private function renderBlocks(array $blocks): string
    {
        if (app()->bound('tp.blocks.render')) {
            $renderer = resolve('tp.blocks.render');

            if (is_callable($renderer)) {
                $html = $renderer($blocks);
                if (is_string($html)) {
                    return $html;
                }
            }
        }

        return (string) view('tentapress-pages::blocks.fallback-list', [
            'blocks' => $blocks,
        ]);
    }

    /**
     * @param  array<string,mixed>|null  $content
     * @param  array<int,mixed>  $blocks
     */
    private function renderPageContent(?array $content, array $blocks, string $editorDriver): string
    {
        if ($editorDriver === 'blocks') {
            return $this->renderBlocks($blocks);
        }

        if (is_array($content) && app()->bound('tp.page_editor.render')) {
            $renderer = resolve('tp.page_editor.render');

            if (is_callable($renderer)) {
                $html = $renderer($content);
                if (is_string($html) && trim($html) !== '') {
                    return $html;
                }
            }
        }

        return $this->renderBlocks($blocks);
    }

    private function resolveLayoutView(string $layoutKey): string
    {
        if (app()->bound(ThemeManager::class)) {
            $themes = resolve(ThemeManager::class);

            $themeView = $themes->layoutView($layoutKey);

            if (is_string($themeView) && $themeView !== '') {
                return $themeView;
            }
        }

        // Plugin fallback
        return 'tentapress-pages::layouts.page';
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
