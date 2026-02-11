<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;

it('renders a published page by slug', function (): void {
    TpPage::query()->create([
        'title' => 'About',
        'slug' => 'about',
        'status' => 'published',
        'blocks' => [],
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('About');
});

it('renders published page block content using the fallback renderer', function (): void {
    TpPage::query()->create([
        'title' => 'Services',
        'slug' => 'services',
        'status' => 'published',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Rendered block body',
                ],
            ],
        ],
    ]);

    $this->get('/services')
        ->assertOk()
        ->assertSee('Services')
        ->assertSee('Rendered block body');
});
