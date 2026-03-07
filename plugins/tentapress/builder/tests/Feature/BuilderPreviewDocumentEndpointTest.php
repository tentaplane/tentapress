<?php

declare(strict_types=1);

use TentaPress\System\Theme\ThemeManager;
use TentaPress\Builder\BuilderServiceProvider;
use TentaPress\GlobalContent\GlobalContentServiceProvider;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\Users\Models\TpUser;

function loadBuilderDocumentClasses(): void
{
    $base = dirname(__DIR__, 2).'/src';

    require_once $base.'/Support/PreviewSnapshotStore.php';
    require_once $base.'/Support/BuilderPreviewBlockRenderer.php';
    require_once $base.'/Support/BuilderPreviewDocumentExtractor.php';
    require_once $base.'/Support/BuilderPreviewDocumentRenderer.php';
    require_once $base.'/Http/Admin/BuilderSnapshotController.php';
    require_once $base.'/Http/Admin/BuilderPreviewDocumentController.php';
    require_once $base.'/BuilderServiceProvider.php';
}

function registerBuilderDocumentProvider(): void
{
    if (! class_exists(BuilderServiceProvider::class)) {
        loadBuilderDocumentClasses();
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

function registerBuilderGlobalContentAutoloader(): void
{
    spl_autoload_register(static function (string $class): void {
        $prefix = 'TentaPress\\GlobalContent\\';

        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        if (! is_string($relativeClass) || $relativeClass === '') {
            return;
        }

        $path = base_path('plugins/tentapress/global-content/src/'.str_replace('\\', '/', $relativeClass).'.php');
        if (is_file($path)) {
            require_once $path;
        }
    });
}

function enableBuilderPreviewDependencies(bool $activateTheme = false): void
{
    registerBuilderGlobalContentAutoloader();
    static $pluginsEnabled = false;

    if (! $pluginsEnabled) {
        test()->artisan('tp:plugins sync')->assertSuccessful();

        foreach ([
            'tentapress/admin-shell',
            'tentapress/blocks',
            'tentapress/builder',
            'tentapress/themes',
            'tentapress/pages',
            'tentapress/posts',
            'tentapress/global-content',
            'tentapress/users',
        ] as $pluginId) {
            test()->artisan('tp:plugins enable '.$pluginId)->assertSuccessful();
        }

        $pluginsEnabled = true;
    }

    if ($activateTheme) {
        test()->artisan('tp:themes sync')->assertSuccessful();

        resolve(ThemeManager::class)->activate('tentapress/tailwind');
        resolve(ThemeManager::class)->registerActiveThemeViews();
        resolve(ThemeManager::class)->registerActiveThemeProvider();
    }

    if (app()->getProvider(GlobalContentServiceProvider::class) === null) {
        app()->register(GlobalContentServiceProvider::class);
        resolve('router')->getRoutes()->refreshNameLookups();
        resolve('router')->getRoutes()->refreshActionLookups();
    }
}

it('returns a valid preview document schema for page snapshots', function (): void {
    enableBuilderPreviewDependencies();
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Page Document',
        'email' => 'builder-page-doc@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'pages',
        'title' => 'Document Preview Page',
        'slug' => 'document-preview-page',
        'layout' => 'default',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Document preview body',
                ],
            ],
        ],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');
    expect($documentUrl)->not->toBe('');

    $response = $this->actingAs($author)->getJson($documentUrl);
    $response->assertOk()->assertJsonStructure([
        'token',
        'revision',
        'lang',
        'body_class',
        'styles',
        'inline_styles',
        'body_html',
        'block_map',
    ]);

    $payload = $response->json();
    expect((string) ($payload['body_html'] ?? ''))->toContain('Document preview body');
    expect((string) ($payload['body_html'] ?? ''))->not->toContain('<script');
    expect($payload['styles'] ?? [])->toBeArray();
    expect($payload['block_map'] ?? [])->toBeArray();
    expect((int) (($payload['block_map'][0]['index'] ?? -1)))->toBe(0);
});

it('strips inline script vectors from preview document body html', function (): void {
    enableBuilderPreviewDependencies();
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Script Safe Document',
        'email' => 'builder-script-safe-doc@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'pages',
        'title' => 'Script Safe Preview',
        'slug' => 'script-safe-preview',
        'layout' => 'default',
        'blocks' => [
            [
                'type' => 'blocks/buttons',
                'props' => [
                    'actions' => [
                        [
                            'label' => 'Unsafe Link',
                            'url' => 'javascript:alert(1)',
                            'style' => 'primary',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');
    expect($documentUrl)->not->toBe('');

    $response = $this->actingAs($author)->getJson($documentUrl);
    $response->assertOk();

    $bodyHtml = (string) $response->json('body_html');
    expect($bodyHtml)->not->toContain('javascript:alert(1)');
    expect($bodyHtml)->toContain('Unsafe Link');
});

it('returns a valid preview document schema for post snapshots', function (): void {
    enableBuilderPreviewDependencies();
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Post Document',
        'email' => 'builder-post-doc@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'posts',
        'title' => 'Document Preview Post',
        'slug' => 'document-preview-post',
        'layout' => 'post',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Document preview post body',
                ],
            ],
        ],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');
    expect($documentUrl)->not->toBe('');

    $response = $this->actingAs($author)->getJson($documentUrl);
    $response->assertOk();

    $payload = $response->json();
    expect((string) ($payload['body_html'] ?? ''))->toContain('Document Preview Post');
    expect((string) ($payload['body_html'] ?? ''))->toContain('Document preview post body');
});

it('denies cross-user access to preview document snapshots', function (): void {
    enableBuilderPreviewDependencies();
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Document Author',
        'email' => 'builder-doc-author@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $other = TpUser::query()->create([
        'name' => 'Builder Document Other',
        'email' => 'builder-doc-other@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'pages',
        'title' => 'Private Document',
        'slug' => 'private-document',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');

    $this->actingAs($other)->getJson($documentUrl)->assertNotFound();
});

it('expires preview document snapshots after ttl', function (): void {
    enableBuilderPreviewDependencies();
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Document Expiry',
        'email' => 'builder-doc-expiry@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'pages',
        'title' => 'Expiry Document',
        'slug' => 'expiry-document',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');

    $this->travel(11)->minutes();

    $this->actingAs($author)->getJson($documentUrl)->assertNotFound();
});

it('returns a valid preview document schema for global content snapshots', function (): void {
    enableBuilderPreviewDependencies(activateTheme: true);
    registerBuilderDocumentProvider();

    $author = TpUser::query()->create([
        'name' => 'Builder Global Content Document',
        'email' => 'builder-global-content-doc@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $snapshot = $this->actingAs($author)->post('/admin/builder/snapshots', [
        'resource' => 'global-content',
        'title' => 'Preview Global Content',
        'slug' => 'preview-global-content',
        'layout' => 'default',
        'blocks' => [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => 'Global content preview body',
                ],
            ],
        ],
    ]);

    $snapshot->assertOk();
    $documentUrl = (string) $snapshot->json('document_url');
    expect($documentUrl)->not->toBe('');

    $response = $this->actingAs($author)->getJson($documentUrl);
    $response->assertOk();

    $payload = $response->json();
    expect((string) ($payload['body_html'] ?? ''))->toContain('Global content preview body');
    expect((string) ($payload['body_html'] ?? ''))->not->toContain('<script');
    expect($payload['styles'] ?? [])->not->toBe([]);
});
