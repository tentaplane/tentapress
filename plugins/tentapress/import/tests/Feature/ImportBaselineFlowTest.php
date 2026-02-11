<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use TentaPress\Import\ImportServiceProvider;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;

function registerImportProvider(): void
{
    app()->register(ImportServiceProvider::class);
    resolve('router')->getRoutes()->refreshNameLookups();
    resolve('router')->getRoutes()->refreshActionLookups();
}

function makeImportBundleWithSinglePage(): UploadedFile
{
    $path = storage_path('framework/testing/tp-import-bundle.zip');
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    throw_if($opened !== true, RuntimeException::class, 'Unable to create import bundle fixture.');

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
                'title' => 'Imported Home',
                'slug' => 'imported-home',
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

it('redirects guests from import admin routes to login', function (): void {
    registerImportProvider();

    $this->get('/admin/import')->assertRedirect('/admin/login');
    $this->post('/admin/import/analyze')->assertRedirect('/admin/login');
    $this->post('/admin/import/run')->assertRedirect('/admin/login');
});

it('allows a super admin to view import index', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-admin@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/import')
        ->assertOk()
        ->assertViewIs('tentapress-import::index');
});

it('allows a super admin to analyze and run an import bundle', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-run@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeImportBundleWithSinglePage();

    $analyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);

    $analyzeResponse
        ->assertOk()
        ->assertViewIs('tentapress-import::review')
        ->assertViewHas('token')
        ->assertViewHas('summary', fn (array $summary): bool => ($summary['pages'] ?? null) === 1);

    $token = (string) $analyzeResponse->viewData('token');

    expect($token)->not->toBe('');

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

    expect(
        TpPage::query()->where('slug', 'imported-home')->exists()
    )->toBeTrue();
});
