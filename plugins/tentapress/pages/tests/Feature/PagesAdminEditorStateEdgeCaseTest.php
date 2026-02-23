<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;
use TentaPress\Users\Models\TpUser;

it('creates unique slugs from duplicate page titles when slug is omitted', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-duplicate-title@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Landing Page',
        'slug' => '',
        'layout' => 'default',
    ])->assertRedirect();

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Landing Page',
        'slug' => '',
        'layout' => 'default',
    ])->assertRedirect();

    expect(TpPage::query()->where('slug', 'landing-page')->exists())->toBeTrue();
    expect(TpPage::query()->where('slug', 'landing-page-2')->exists())->toBeTrue();
});

it('redirects back to editor when return_to editor is requested on update', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-return-editor@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $page = TpPage::query()->create([
        'title' => 'Editor Return Page',
        'slug' => 'editor-return-page',
        'status' => 'draft',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $this->actingAs($admin)
        ->put('/admin/pages/'.$page->id, [
            'title' => 'Editor Return Page Updated',
            'slug' => 'editor-return-page',
            'layout' => 'default',
            'editor_driver' => 'blocks',
            'blocks_json' => '[]',
            'return_to' => 'editor',
        ])
        ->assertRedirect('/admin/pages/'.$page->id.'/editor')
        ->assertSessionHas('tp_notice_success', 'Page updated.');
});

it('keeps existing published_at timestamp when publishing an already published page', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Admin',
        'email' => 'pages-republish@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $originalPublishedAt = now()->subDay();

    $page = TpPage::query()->create([
        'title' => 'Republish Page',
        'slug' => 'republish-page',
        'status' => 'published',
        'layout' => 'default',
        'blocks' => [],
        'published_at' => $originalPublishedAt,
    ]);

    $this->actingAs($admin)
        ->post('/admin/pages/'.$page->id.'/publish')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Page published.');

    $page->refresh();

    expect($page->published_at?->toDateTimeString())
        ->toBe($originalPublishedAt->toDateTimeString());
});

it('normalizes nested split-layout child blocks on page save', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Nested Admin',
        'email' => 'pages-nested-layout@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $payload = [
        [
            'type' => 'blocks/split-layout',
            'props' => [
                'ratio' => '60-40',
                'left_blocks' => [
                    [
                        'type' => 'blocks/content',
                        'props' => [
                            'content' => 'Nested left content',
                        ],
                    ],
                    [
                        'type' => 'blocks/split-layout',
                        'props' => [
                            'left_blocks' => [
                                [
                                    'type' => 'blocks/content',
                                    'props' => ['content' => 'Should be dropped'],
                                ],
                            ],
                            'right_blocks' => [],
                        ],
                    ],
                ],
                'right_blocks' => [
                    [
                        'type' => 'blocks/content',
                        'props' => [
                            'content' => 'Nested right content',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Nested Layout Page',
        'slug' => '',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => json_encode($payload, JSON_THROW_ON_ERROR),
        'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
    ])->assertRedirect();

    $page = TpPage::query()->where('title', 'Nested Layout Page')->firstOrFail();
    $blocks = is_array($page->blocks) ? $page->blocks : [];

    expect($blocks)->toHaveCount(1);
    expect($blocks[0]['type'] ?? null)->toBe('blocks/split-layout');
    expect(($blocks[0]['props']['left_blocks'] ?? []))->toHaveCount(1);
    expect($blocks[0]['props']['left_blocks'][0]['type'] ?? null)->toBe('blocks/content');
    expect($blocks[0]['props']['right_blocks'][0]['type'] ?? null)->toBe('blocks/content');
});

it('accepts builder as an editor driver when registered', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Builder Admin',
        'email' => 'pages-builder-driver@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    /** @var EditorDriverRegistry $registry */
    $registry = app()->make(EditorDriverRegistry::class);
    $registry->register(new EditorDriverDefinition(
        id: 'builder',
        label: 'Visual Builder',
        description: 'Test builder driver.',
        storage: 'blocks',
        usesBlocksEditor: true,
        sortOrder: 30,
    ));

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Builder Driver Page',
        'slug' => '',
        'layout' => 'default',
        'editor_driver' => 'builder',
        'blocks_json' => '[]',
    ])->assertRedirect();

    $page = TpPage::query()->where('title', 'Builder Driver Page')->firstOrFail();

    expect((string) $page->editor_driver)->toBe('builder');
});

it('normalizes presentation metadata to the supported whitelist on page save', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Pages Presentation Admin',
        'email' => 'pages-presentation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $payload = [
        [
            'type' => 'blocks/content',
            'props' => [
                'content' => 'Presentation test',
                'presentation' => [
                    'container' => 'wide',
                    'align' => 'center',
                    'background' => 'brand',
                    'spacing' => [
                        'top' => 'lg',
                        'bottom' => 'invalid',
                    ],
                    'script' => 'alert(1)',
                ],
            ],
        ],
    ];

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Presentation Page',
        'slug' => '',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => json_encode($payload, JSON_THROW_ON_ERROR),
    ])->assertRedirect();

    $page = TpPage::query()->where('title', 'Presentation Page')->firstOrFail();
    $blocks = is_array($page->blocks) ? $page->blocks : [];
    $presentation = $blocks[0]['props']['presentation'] ?? null;

    expect($presentation)->toBe([
        'container' => 'wide',
        'align' => 'center',
        'background' => 'brand',
        'spacing' => [
            'top' => 'lg',
        ],
    ]);
});
