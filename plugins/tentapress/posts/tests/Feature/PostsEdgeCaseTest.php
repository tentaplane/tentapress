<?php

declare(strict_types=1);

use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;
use TentaPress\Users\Models\TpUser;

it('does not expose draft posts on public routes', function (): void {
    TpPost::query()->create([
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'status' => 'draft',
    ]);

    $this->get('/blog/draft-post')->assertNotFound();
    $this->get('/blog')->assertDontSee('Draft Post');
});

it('does not expose posts scheduled in the future', function (): void {
    TpPost::query()->create([
        'title' => 'Scheduled Post',
        'slug' => 'scheduled-post',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/blog/scheduled-post')->assertNotFound();
    $this->get('/blog')->assertDontSee('Scheduled Post');
});

it('returns not found for unknown public post slugs', function (): void {
    $this->get('/blog/non-existent-post')->assertNotFound();
});

it('denies posts admin index access to non-super-admin users without capability', function (): void {
    $user = TpUser::query()->create([
        'name' => 'Regular User',
        'email' => 'posts-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/posts')
        ->assertForbidden();
});

it('normalizes nested split-layout child blocks on post save', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Posts Nested Admin',
        'email' => 'posts-nested-layout@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $payload = [
        [
            'type' => 'blocks/split-layout',
            'props' => [
                'left_blocks' => [
                    [
                        'type' => 'blocks/content',
                        'props' => [
                            'content' => 'Nested left post content',
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
                            'content' => 'Nested right post content',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Nested Layout Post',
        'slug' => '',
        'editor_driver' => 'blocks',
        'blocks_json' => json_encode($payload, JSON_THROW_ON_ERROR),
        'page_doc_json' => '{"time":0,"blocks":[],"version":"2.28.0"}',
    ])->assertRedirect();

    $post = TpPost::query()->where('title', 'Nested Layout Post')->firstOrFail();
    $blocks = is_array($post->blocks) ? $post->blocks : [];

    expect($blocks)->toHaveCount(1);
    expect($blocks[0]['type'] ?? null)->toBe('blocks/split-layout');
    expect(($blocks[0]['props']['left_blocks'] ?? []))->toHaveCount(1);
    expect($blocks[0]['props']['left_blocks'][0]['type'] ?? null)->toBe('blocks/content');
    expect($blocks[0]['props']['right_blocks'][0]['type'] ?? null)->toBe('blocks/content');
});

it('accepts builder as an editor driver when registered', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Posts Builder Admin',
        'email' => 'posts-builder-driver@example.test',
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

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Builder Driver Post',
        'slug' => '',
        'editor_driver' => 'builder',
        'blocks_json' => '[]',
    ])->assertRedirect();

    $post = TpPost::query()->where('title', 'Builder Driver Post')->firstOrFail();

    expect((string) $post->editor_driver)->toBe('builder');
});

it('normalizes presentation metadata to the supported whitelist on post save', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Posts Presentation Admin',
        'email' => 'posts-presentation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $payload = [
        [
            'type' => 'blocks/content',
            'props' => [
                'content' => 'Presentation test',
                'presentation' => [
                    'container' => 'full',
                    'align' => 'right',
                    'background' => 'muted',
                    'spacing' => [
                        'top' => 'xs',
                        'bottom' => 'md',
                    ],
                    'extra' => ['foo' => 'bar'],
                ],
            ],
        ],
    ];

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Presentation Post',
        'slug' => '',
        'editor_driver' => 'blocks',
        'blocks_json' => json_encode($payload, JSON_THROW_ON_ERROR),
    ])->assertRedirect();

    $post = TpPost::query()->where('title', 'Presentation Post')->firstOrFail();
    $blocks = is_array($post->blocks) ? $post->blocks : [];
    $presentation = $blocks[0]['props']['presentation'] ?? null;

    expect($presentation)->toBe([
        'container' => 'full',
        'align' => 'right',
        'background' => 'muted',
        'spacing' => [
            'top' => 'xs',
            'bottom' => 'md',
        ],
    ]);
});
