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
        $blocks = is_array($page->blocks) ? $page->blocks : [];
        $blocksHtml = $this->renderBlocks($blocks);
        $view = $this->resolveLayoutView($layoutKey);

        return response()->view($view, [
            'page' => $page,
            'layoutKey' => $layoutKey,
            'blocksHtml' => $blocksHtml,
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
}
