<?php

declare(strict_types=1);

namespace TentaPress\RichText;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final class RichTextServiceProvider extends ServiceProvider
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
            type: 'blocks/rich-text',
            name: 'Rich Text',
            description: 'Free-form rich text with HTML output.',
            version: 1,
            fields: [
                [
                    'key' => 'content',
                    'label' => 'Content',
                    'type' => 'richtext',
                    'help' => 'Formatting, lists, and links are supported.',
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
                'content' => '<p>Write your content here.</p>',
                'width' => 'normal',
                'alignment' => 'left',
                'background' => 'white',
            ],
            example: [
                'props' => [
                    'content' => implode('', [
                        '<p><strong>Rich text</strong> lets you format freely.</p>',
                        '<ul><li>Bold, italic, links</li><li>Lists and paragraphs</li></ul>',
                    ]),
                    'width' => 'normal',
                    'alignment' => 'left',
                    'background' => 'white',
                ],
            ],
            view: 'blocks.rich-text',
        ));

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');
    }
}
