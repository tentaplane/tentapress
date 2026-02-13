<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use TentaPress\Import\ImportServiceProvider;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;

function registerImportProviderForEdgeCases(): void
{
    app()->register(ImportServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

function makeImportBundleForSlug(string $slug): UploadedFile
{
    $path = storage_path('framework/testing/tp-import-edge-bundle.zip');
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    throw_if($opened !== true, RuntimeException::class, 'Unable to create import edge fixture.');

    $manifest = [
        'schema_version' => 1,
        'generated_at_utc' => now()->toIso8601String(),
        'includes' => [
            'pages' => true,
            'posts' => false,
            'media' => false,
            'settings' => false,
            'theme' => false,
            'plugins' => false,
            'seo' => false,
        ],
    ];

    $pages = [
        'count' => 1,
        'items' => [
            [
                'title' => 'Edge Imported Page',
                'slug' => $slug,
                'status' => 'published',
                'layout' => 'default',
                'blocks' => [],
            ],
        ],
    ];

    $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
    $zip->addFromString('pages.json', json_encode($pages, JSON_THROW_ON_ERROR));
    $zip->close();

    return new UploadedFile($path, 'bundle.zip', 'application/zip', null, true);
}

it('denies import routes to non-super-admin users', function (): void {
    registerImportProviderForEdgeCases();

    $user = TpUser::query()->create([
        'name' => 'Import Regular User',
        'email' => 'import-regular@example.test',
        'password' => 'secret',
        'is_super_admin' => false,
    ]);

    $this->actingAs($user)
        ->get('/admin/import')
        ->assertForbidden();

    $this->actingAs($user)
        ->post('/admin/import/analyze')
        ->assertForbidden();

    $this->actingAs($user)
        ->post('/admin/import/run')
        ->assertForbidden();
});

it('validates that analyze requires a zip or xml bundle', function (): void {
    registerImportProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-analyze-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/import')
        ->post('/admin/import/analyze', [
            'bundle' => UploadedFile::fake()->create('not-a-zip.txt', 10, 'text/plain'),
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHasErrors(['bundle']);
});

it('shows actionable error when wxr xml is malformed', function (): void {
    registerImportProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-bad-xml@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $path = storage_path('framework/testing/tp-import-invalid.xml');
    File::ensureDirectoryExists(dirname($path));
    File::put(
        $path,
        '<?xml version="1.0" encoding="UTF-8" ?><rss><channel><item><title>Bad</title><wp:post_type>post</wp:post_type></item>'
    );

    $bundle = new UploadedFile($path, 'invalid.xml', 'text/xml', null, true);

    $this->withoutExceptionHandling();

    expect(fn (): \Illuminate\Testing\TestResponse => $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]))
        ->toThrow(RuntimeException::class, 'Invalid WXR XML');
});

it('validates required run payload fields and boolean include flags', function (): void {
    registerImportProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-run-validation@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->from('/admin/import')
        ->post('/admin/import/run', [
            'token' => '',
            'pages_mode' => 'replace',
            'settings_mode' => 'invalid',
            'include_posts' => 'yes',
            'include_media' => 'no',
            'include_seo' => 'maybe',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHasErrors([
            'token',
            'pages_mode',
            'settings_mode',
            'include_posts',
            'include_media',
            'include_seo',
        ]);
});

it('creates a unique page slug when imported page slug already exists', function (): void {
    registerImportProviderForEdgeCases();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-slug-conflict@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    TpPage::query()->create([
        'title' => 'Existing Page',
        'slug' => 'imported-home',
        'status' => 'published',
        'layout' => 'default',
        'blocks' => [],
    ]);

    $bundle = makeImportBundleForSlug('imported-home');

    $analyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);

    $token = (string) $analyzeResponse->viewData('token');

    $this->actingAs($admin)
        ->post('/admin/import/run', [
            'token' => $token,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '0',
            'include_media' => '0',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHas('tp_notice_success');

    expect(TpPage::query()->where('slug', 'imported-home')->exists())->toBeTrue();
    expect(TpPage::query()->where('slug', 'imported-home-2')->exists())->toBeTrue();
});
