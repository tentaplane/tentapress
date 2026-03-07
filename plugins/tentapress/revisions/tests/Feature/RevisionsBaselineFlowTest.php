<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionRecorder;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/revisions')->assertSuccessful();
});

it('captures page revisions on create and update and renders the metabox', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Revision Admin',
        'email' => 'revisions-pages@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)->post('/admin/pages', [
        'title' => 'Revision Ready Page',
        'slug' => 'revision-ready-page',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Initial"}}]',
        'page_doc_json' => '{"time":1,"blocks":[{"id":"a","type":"paragraph","data":{"text":"Initial"}}],"version":"2.28.0"}',
    ])->assertRedirect();

    $page = TpPage::query()->where('slug', 'revision-ready-page')->firstOrFail();

    expect(TpRevision::query()->where('resource_type', 'pages')->where('resource_id', $page->id)->count())->toBe(1);

    $this->actingAs($admin)->put('/admin/pages/'.$page->id, [
        'title' => 'Revision Ready Page',
        'slug' => 'revision-ready-page',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Updated"}}]',
        'page_doc_json' => '{"time":2,"blocks":[{"id":"b","type":"paragraph","data":{"text":"Updated"}}],"version":"2.28.0"}',
    ])->assertRedirect('/admin/pages/'.$page->id.'/edit');

    expect(TpRevision::query()->where('resource_type', 'pages')->where('resource_id', $page->id)->count())->toBe(2);

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertSee('Revisions')
        ->assertSee('revision-ready-page')
        ->assertSee('Saved by:')
        ->assertSee('Revision Admin');
});

it('captures post revisions without duplicating unchanged saves', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Post Revision Admin',
        'email' => 'revisions-posts@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Revision Post',
        'slug' => 'revision-post',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Initial post"}}]',
        'page_doc_json' => '{"time":1,"blocks":[{"id":"p1","type":"paragraph","data":{"text":"Initial post"}}],"version":"2.28.0"}',
    ])->assertRedirect();

    $post = TpPost::query()->where('slug', 'revision-post')->firstOrFail();

    $this->actingAs($admin)->put('/admin/posts/'.$post->id, [
        'title' => 'Revision Post',
        'slug' => 'revision-post',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Initial post"}}]',
        'page_doc_json' => '{"time":1,"blocks":[{"id":"p1","type":"paragraph","data":{"text":"Initial post"}}],"version":"2.28.0"}',
        'author_id' => $admin->id,
        'published_at' => '',
    ])->assertRedirect('/admin/posts/'.$post->id.'/edit');

    expect(TpRevision::query()->where('resource_type', 'posts')->where('resource_id', $post->id)->count())->toBe(1);

    $this->actingAs($admin)->put('/admin/posts/'.$post->id, [
        'title' => 'Revision Post',
        'slug' => 'revision-post',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Updated post"}}]',
        'page_doc_json' => '{"time":2,"blocks":[{"id":"p2","type":"paragraph","data":{"text":"Updated post"}}],"version":"2.28.0"}',
        'author_id' => $admin->id,
        'published_at' => '',
    ])->assertRedirect('/admin/posts/'.$post->id.'/edit');

    expect(TpRevision::query()->where('resource_type', 'posts')->where('resource_id', $post->id)->count())->toBe(2);

    $this->actingAs($admin)
        ->get('/admin/posts/'.$post->id.'/edit')
        ->assertOk()
        ->assertSee('Revisions')
        ->assertSee('revision-post')
        ->assertSee('Author ID:')
        ->assertSee((string) $admin->id);
});

it('treats duplicate manual snapshot inserts as a no-op when the unique row already exists', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Duplicate Revision Admin',
        'email' => 'duplicate-revision-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $pagePayload = [
        'title' => 'Duplicate Safe Page',
        'slug' => 'duplicate-safe-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'builder',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Duplicate safe body'],
            ],
        ],
        'published_at' => now()->subMinute(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ];

    if (Schema::hasColumn('tp_pages', 'content')) {
        $pagePayload['content'] = ['time' => 1, 'blocks' => [], 'version' => '2.31.1'];
    }

    $page = TpPage::query()->create($pagePayload);

    $firstRevision = resolve(RevisionRecorder::class)->capturePage($page);

    expect($firstRevision)->not->toBeNull();
    expect(TpRevision::query()->where('resource_type', 'pages')->where('resource_id', $page->id)->count())->toBe(1);

    TpRevision::query()->whereKey($firstRevision->id)->delete();

    TpRevision::query()->create([
        'resource_type' => 'pages',
        'resource_id' => (int) $page->id,
        'revision_kind' => 'manual',
        'title' => (string) $page->title,
        'slug' => (string) $page->slug,
        'status' => (string) $page->status,
        'layout' => $page->layout !== null ? (string) $page->layout : null,
        'editor_driver' => (string) $page->editor_driver,
        'blocks' => is_array($page->blocks) ? $page->blocks : [],
        'content' => is_array($page->content) ? $page->content : null,
        'author_id' => null,
        'published_at' => $page->published_at,
        'created_by' => $admin->id,
        'restored_from_revision_id' => null,
        'snapshot_hash' => (string) $firstRevision->snapshot_hash,
    ]);

    $duplicate = resolve(RevisionRecorder::class)->capturePage($page);

    expect($duplicate)->toBeNull();
    expect(TpRevision::query()->where('resource_type', 'pages')->where('resource_id', $page->id)->count())->toBe(1);
});

it('persists autosave drafts and reloads them in the page editor', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Autosave Admin',
        'email' => 'autosave-pages@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $pagePayload = [
        'title' => 'Autosave Page',
        'slug' => 'autosave-page',
        'status' => 'draft',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [],
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ];
    if (Schema::hasColumn('tp_pages', 'content')) {
        $pagePayload['content'] = ['time' => 0, 'blocks' => [], 'version' => '2.28.0'];
    }

    $page = TpPage::query()->create($pagePayload);

    $this->actingAs($admin)
        ->postJson('/admin/pages/'.$page->id.'/revisions/autosave', [
            'title' => 'Autosave Draft Title',
            'slug' => 'autosave-page',
            'layout' => 'landing',
            'editor_driver' => 'blocks',
            'blocks_json' => '[{"type":"blocks/content","props":{"body":"Autosaved page content"}}]',
            'page_doc_json' => '{"time":5,"blocks":[{"id":"ap1","type":"paragraph","data":{"text":"Autosaved page content"}}],"version":"2.28.0"}',
        ])
        ->assertOk()
        ->assertJsonPath('revision_kind', 'autosave');

    $autosave = TpRevision::query()
        ->where('resource_type', 'pages')
        ->where('resource_id', $page->id)
        ->where('revision_kind', 'autosave')
        ->latest('id')
        ->first();

    expect($autosave)->not->toBeNull();

    $this->actingAs($admin)
        ->get('/admin/pages/'.$page->id.'/edit')
        ->assertOk()
        ->assertSee('Loaded autosave draft')
        ->assertSee('Autosave Draft Title')
        ->assertSee('landing');
});

it('renders a compare view for post revisions with changed fields', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Compare Admin',
        'email' => 'compare-posts@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Compare Post',
        'slug' => 'compare-post',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"Before"}}]',
        'page_doc_json' => '{"time":1,"blocks":[{"id":"c1","type":"paragraph","data":{"text":"Before"}}],"version":"2.28.0"}',
    ])->assertRedirect();

    $post = TpPost::query()->where('slug', 'compare-post')->firstOrFail();

    $this->actingAs($admin)->put('/admin/posts/'.$post->id, [
        'title' => 'Compare Post Updated',
        'slug' => 'compare-post',
        'layout' => 'post',
        'editor_driver' => 'blocks',
        'blocks_json' => '[{"type":"blocks/content","props":{"body":"After"}}]',
        'page_doc_json' => '{"time":2,"blocks":[{"id":"c2","type":"paragraph","data":{"text":"After"}}],"version":"2.28.0"}',
        'author_id' => $admin->id,
        'published_at' => '',
    ])->assertRedirect('/admin/posts/'.$post->id.'/edit');

    $revisions = TpRevision::query()
        ->where('resource_type', 'posts')
        ->where('resource_id', $post->id)
        ->latest('id')
        ->get();

    $this->actingAs($admin)
        ->get('/admin/posts/'.$post->id.'/revisions/compare?left='.$revisions[1]->id.'&right='.$revisions[0]->id)
        ->assertOk()
        ->assertSee('Post revision compare')
        ->assertSee('Changed fields')
        ->assertSee('Title')
        ->assertSee('Compare Post Updated');
});

it('restores revisions and updates public and api rendering', function (): void {
    $admin = TpUser::query()->create([
        'name' => 'Restore Admin',
        'email' => 'restore-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $pagePayload = [
        'title' => 'Restore Page',
        'slug' => 'restore-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Original page body'],
            ],
        ],
        'published_at' => now()->subMinute(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ];
    if (Schema::hasColumn('tp_pages', 'content')) {
        $pagePayload['content'] = ['time' => 0, 'blocks' => [], 'version' => '2.28.0'];
    }

    $page = TpPage::query()->create($pagePayload);

    $postPayload = [
        'title' => 'Restore Post',
        'slug' => 'restore-post',
        'status' => 'published',
        'layout' => 'post',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Original post body'],
            ],
        ],
        'published_at' => now()->subMinute(),
        'author_id' => $admin->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ];
    if (Schema::hasColumn('tp_posts', 'content')) {
        $postPayload['content'] = ['time' => 0, 'blocks' => [], 'version' => '2.28.0'];
    }

    $post = TpPost::query()->create($postPayload);

    $pageOld = TpRevision::query()->create([
        'resource_type' => 'pages',
        'resource_id' => $page->id,
        'revision_kind' => 'manual',
        'title' => 'Restore Page',
        'slug' => 'restore-page',
        'status' => 'published',
        'layout' => 'default',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Restored page body'],
            ],
        ],
        'content' => null,
        'author_id' => null,
        'published_at' => now()->subMinute(),
        'created_by' => $admin->id,
        'snapshot_hash' => sha1('page-old'),
    ]);

    $postOld = TpRevision::query()->create([
        'resource_type' => 'posts',
        'resource_id' => $post->id,
        'revision_kind' => 'manual',
        'title' => 'Restore Post',
        'slug' => 'restore-post',
        'status' => 'published',
        'layout' => 'post',
        'editor_driver' => 'blocks',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => ['content' => 'Restored post body'],
            ],
        ],
        'content' => null,
        'author_id' => $admin->id,
        'published_at' => now()->subMinute(),
        'created_by' => $admin->id,
        'snapshot_hash' => sha1('post-old'),
    ]);

    $this->actingAs($admin)
        ->post('/admin/pages/'.$page->id.'/revisions/'.$pageOld->id.'/restore')
        ->assertRedirect('/admin/pages/'.$page->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Revision restored.');

    $this->actingAs($admin)
        ->post('/admin/posts/'.$post->id.'/revisions/'.$postOld->id.'/restore')
        ->assertRedirect('/admin/posts/'.$post->id.'/edit')
        ->assertSessionHas('tp_notice_success', 'Revision restored.');

    $this->get('/restore-page')
        ->assertOk()
        ->assertSee('Restored page body');

    $this->get('/blog/restore-post')
        ->assertOk()
        ->assertSee('Restored post body');

    $this->getJson('/api/v1/pages/restore-page')
        ->assertOk()
        ->assertJsonPath('data.slug', 'restore-page');

    $this->getJson('/api/v1/posts/restore-post')
        ->assertOk()
        ->assertJsonPath('data.slug', 'restore-post');

    expect(TpRevision::query()->where('resource_type', 'pages')->where('revision_kind', 'restore')->exists())->toBeTrue();
    expect(TpRevision::query()->where('resource_type', 'posts')->where('revision_kind', 'restore')->exists())->toBeTrue();
});
