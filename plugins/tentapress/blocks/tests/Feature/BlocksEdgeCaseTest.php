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

it('applies presentation wrapper styles for whitelisted settings', function (): void {
    $render = resolve('tp.blocks.render');

    $html = $render([
        [
            'type' => 'blocks/content',
            'props' => [
                'content' => 'Presentation wrapper',
                'presentation' => [
                    'container' => 'wide',
                    'align' => 'center',
                    'background' => 'muted',
                    'spacing' => [
                        'top' => 'sm',
                        'bottom' => 'lg',
                    ],
                ],
            ],
        ],
    ]);

    expect($html)->toContain('tp-block-presentation');
    expect($html)->toContain('margin-top:1rem');
    expect($html)->toContain('margin-bottom:2rem');
    expect($html)->toContain('text-align:center');
    expect($html)->toContain('background-color:#f8fafc');
    expect($html)->toContain('max-width:96rem');
});
