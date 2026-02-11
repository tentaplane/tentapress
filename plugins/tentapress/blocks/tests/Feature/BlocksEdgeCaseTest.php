<?php

declare(strict_types=1);

it('returns empty output for unsupported block types without matching views', function (): void {
    $render = resolve('tp.blocks.render');

    $html = $render([
        [
            'type' => 'vendor/non-existent-block',
            'props' => [
                'content' => 'Should not render',
            ],
        ],
    ]);

    expect($html)->toBe('');
});

it('skips malformed payload entries while rendering valid blocks', function (): void {
    $render = resolve('tp.blocks.render');

    $html = $render([
        'blocks' => [
            'not-an-array',
            123,
            [
                'props' => ['content' => 'Missing type'],
            ],
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Valid block remains rendered',
                ],
            ],
        ],
    ]);

    expect($html)->toContain('Valid block remains rendered');
});
