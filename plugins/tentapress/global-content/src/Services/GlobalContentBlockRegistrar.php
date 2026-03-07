<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final readonly class GlobalContentBlockRegistrar
{
    public function __construct(
        private GlobalContentReferenceResolver $resolver,
    ) {
    }

    public function register(BlockRegistry $registry): void
    {
        $options = Schema::hasTable('tp_global_contents') ? $this->resolver->publishedLibrary() : [];

        $registry->register(new BlockDefinition(
            type: 'tentapress/global-content/reference',
            name: 'Global Content Reference',
            description: 'Render a synced global content section or template part by reference.',
            version: 1,
            fields: [
                [
                    'key' => 'global_content_id',
                    'label' => 'Global content',
                    'type' => 'select',
                    'options' => $options,
                    'help' => 'Choose a published global content entry to render by reference.',
                ],
                [
                    'key' => 'global_content_label',
                    'label' => 'Reference label',
                    'type' => 'text',
                    'help' => 'Optional cached label for editor summaries.',
                ],
            ],
            defaults: [
                'global_content_id' => '',
                'global_content_label' => '',
            ],
            example: [
                'props' => [
                    'global_content_id' => isset($options[0]['value']) ? (string) $options[0]['value'] : '',
                    'global_content_label' => isset($options[0]['label']) ? (string) $options[0]['label'] : '',
                ],
            ],
            view: 'global-content.reference',
        ));
    }
}
