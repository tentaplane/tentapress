<?php

declare(strict_types=1);

namespace TentaPress\BlockMarkdownEditor;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final class BlockMarkdownEditorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists(BlockRegistry::class)) {
            return;
        }

        if (! $this->app->bound(BlockRegistry::class)) {
            return;
        }

        $registry = $this->app->make(BlockRegistry::class);

        $registry->register(new BlockDefinition(
            type: 'blocks/markdown',
            name: 'Markdown',
            description: 'A Markdown block with a visual editor.',
            version: 1,
            fields: [
                [
                    'key' => 'content',
                    'label' => 'Content',
                    'type' => 'markdown',
                    'rows' => 12,
                    'height' => '320px',
                    'help' => 'Markdown is supported. HTML is stripped on render.',
                ],
                [
                    'key' => 'width',
                    'label' => 'Content Width',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'narrow', 'label' => 'Narrow'],
                        ['value' => 'normal', 'label' => 'Normal'],
                        ['value' => 'wide', 'label' => 'Wide'],
                    ],
                ],
                [
                    'key' => 'alignment',
                    'label' => 'Text Alignment',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                    ],
                ],
                [
                    'key' => 'background',
                    'label' => 'Background',
                    'type' => 'select',
                    'options' => [
                        ['value' => 'white', 'label' => 'White'],
                        ['value' => 'muted', 'label' => 'Muted'],
                        ['value' => 'none', 'label' => 'None'],
                    ],
                ],
            ],
            defaults: [
                'content' => "",
                'width' => 'normal',
                'alignment' => 'left',
                'background' => 'white',
            ],
            example: [
                'props' => [
                    'content' => "",
                    'width' => 'normal',
                    'alignment' => 'left',
                    'background' => 'white',
                ],
            ],
            view: 'blocks.markdown',
        ));

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');
    }
}
