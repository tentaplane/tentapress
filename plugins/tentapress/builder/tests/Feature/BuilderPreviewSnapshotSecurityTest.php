<?php

declare(strict_types=1);

use TentaPress\Builder\BuilderServiceProvider;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\Builder\Support\PreviewSnapshotStore;
use TentaPress\Users\Models\TpUser;

function loadBuilderClasses(): void
{
    $base = dirname(__DIR__, 2).'/src';

    require_once $base.'/Support/PreviewSnapshotStore.php';
    require_once $base.'/Http/Admin/BuilderSnapshotController.php';
    require_once $base.'/Http/Admin/BuilderPreviewController.php';
    require_once $base.'/BuilderServiceProvider.php';
}

function registerBuilderProvider(): void
{
    if (! class_exists(BuilderServiceProvider::class)) {
        loadBuilderClasses();
    }

    if (app()->bound(PluginRegistry::class)) {
        app()->make(PluginRegistry::class)->clearCache();
    }

    if (app()->getProvider(BuilderServiceProvider::class) === null) {
        app()->register(BuilderServiceProvider::class);
        resolve('router')->getRoutes()->refreshNameLookups();
        resolve('router')->getRoutes()->refreshActionLookups();
    }
}

it('stores preview snapshots per-user and expires them after ttl', function (): void {
    if (! class_exists(PreviewSnapshotStore::class)) {
        loadBuilderClasses();
    }

    $store = app()->make(PreviewSnapshotStore::class);

    $token = $store->put(101, [
        'resource' => 'pages',
        'title' => 'Token Test',
        'layout' => 'default',
        'blocks' => [],
    ]);

    expect($store->get($token, 101))->toBeArray();
    expect($store->get($token, 202))->toBeNull();

    $this->travel(11)->minutes();

    expect($store->get($token, 101))->toBeNull();
});

it('denies cross-user access to preview snapshots', function (): void {
    registerBuilderProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Author',
        'email' => 'builder-author@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $other = TpUser::query()->create([
        'name' => 'Builder Other',
        'email' => 'builder-other@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)
        ->post('/admin/builder/snapshots', [
            'resource' => 'pages',
            'title' => 'Preview Page',
            'slug' => 'preview-page',
            'layout' => 'default',
            'blocks' => [
                [
                    'type' => 'blocks/content',
                    'props' => [
                        'content' => 'Private preview payload',
                    ],
                ],
            ],
        ]);

    $snapshot->assertOk();

    $previewUrl = (string) $snapshot->json('preview_url');
    expect($previewUrl)->not->toBe('');

    $this->actingAs($author)
        ->get($previewUrl)
        ->assertOk()
        ->assertSee('Private preview payload');

    $this->actingAs($other)
        ->get($previewUrl)
        ->assertNotFound();
});
