<?php

declare(strict_types=1);

use TentaPress\Blocks\Registry\BlockRegistry;

it('registers first-party block definitions in the registry', function (): void {
    $registry = resolve(BlockRegistry::class);

    $definitions = $registry->all();

    expect($definitions)->not->toBeEmpty();
    expect($registry->get('blocks/content'))->not->toBeNull();
    expect($registry->get('blocks/hero'))->not->toBeNull();
});

it('renders content blocks through the tp.blocks.render hook', function (): void {
    $render = resolve('tp.blocks.render');

    $html = $render([
        [
            'type' => 'blocks/content',
            'props' => [
                'content' => 'Rendered from block hook',
            ],
        ],
    ]);

    expect($html)->toContain('Rendered from block hook');
});

it('supports nested blocks payload shape for rendering', function (): void {
    $render = resolve('tp.blocks.render');

    $html = $render([
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Nested block payload',
                ],
            ],
        ],
    ]);

    expect($html)->toContain('Nested block payload');
});
