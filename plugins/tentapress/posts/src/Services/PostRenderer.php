<?php

declare(strict_types=1);

namespace TentaPress\Posts\Services;

use Illuminate\Http\Response;
use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Theme\ThemeManager;

final class PostRenderer
{
    public function render(TpPost $post): Response
    {
        $layoutKey = (string) ($post->layout ?: 'default');
        $blocks = is_array($post->blocks) ? $post->blocks : [];
        $blocksHtml = $this->renderBlocks($blocks);
        $view = $this->resolveLayoutView($layoutKey);

        return response()->view($view, [
            'post' => $post,
            'page' => $post,
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

        return (string) view('tentapress-posts::blocks.fallback-list', [
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

        return 'tentapress-posts::layouts.post';
    }
}
