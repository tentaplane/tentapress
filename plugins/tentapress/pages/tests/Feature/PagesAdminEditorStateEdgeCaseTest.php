<?php

declare(strict_types=1);

use TentaPress\Pages\Models\TpPage;
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
