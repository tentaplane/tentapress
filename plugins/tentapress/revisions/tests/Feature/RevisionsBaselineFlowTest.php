<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Users\Models\TpUser;

beforeEach(function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/revisions')->assertSuccessful();
    $this->refreshApplication();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
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
