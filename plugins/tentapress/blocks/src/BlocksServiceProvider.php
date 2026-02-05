<?php

declare(strict_types=1);

namespace TentaPress\Blocks;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\FirstParty\BasicKit;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Blocks\Render\BlockRenderer;

final class BlocksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BlockRegistry::class);
        $this->app->singleton(BasicKit::class);
        $this->app->singleton(BlockRenderer::class);

        // Bind renderer hook used by pages/posts/theme front-end rendering.
        $this->app->bind('tp.blocks.render', fn (): callable => function (array $blocks): string {
            $renderer = resolve(BlockRenderer::class);
            $items = $blocks;
            if (array_key_exists('blocks', $items) && is_array($items['blocks'])) {
                $items = $items['blocks'];
            }

            $html = '';

            foreach ($items as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $html .= $renderer->render($block);
            }

            return $html;
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');

        $registry = $this->app->make(BlockRegistry::class);
        $this->app->make(BasicKit::class)->register($registry);
    }
}
