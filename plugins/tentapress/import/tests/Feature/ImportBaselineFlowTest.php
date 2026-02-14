<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Posts\Models\TpPost;
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

function makeWxrBundleWithPagePostAndAttachment(): UploadedFile
{
    $path = storage_path('framework/testing/tp-import-bundle.xml');
    File::ensureDirectoryExists(dirname($path));

    $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:wp="http://wordpress.org/export/1.2/">
    <channel>
        <title>Fixture Site</title>
        <wp:wxr_version>1.2</wp:wxr_version>
        <wp:category>
            <wp:term_id>1</wp:term_id>
            <wp:cat_name><![CDATA[News]]></wp:cat_name>
        </wp:category>
        <wp:tag>
            <wp:term_id>2</wp:term_id>
            <wp:tag_name><![CDATA[Release]]></wp:tag_name>
        </wp:tag>
        <item>
            <title>Page Title</title>
            <wp:post_id>101</wp:post_id>
            <wp:post_type>page</wp:post_type>
            <wp:post_name>wxr-page-title</wp:post_name>
            <wp:status>publish</wp:status>
            <link>https://legacy.example.com/page-title</link>
            <content:encoded><![CDATA[<p>Page content line 1.</p><p>Page content line 2.</p>]]></content:encoded>
        </item>
        <item>
            <title>Post Title</title>
            <wp:post_id>102</wp:post_id>
            <wp:post_type>post</wp:post_type>
            <wp:post_name>wxr-post-title</wp:post_name>
            <wp:status>publish</wp:status>
            <wp:post_date_gmt>2025-10-12 14:00:00</wp:post_date_gmt>
            <link>https://legacy.example.com/post-title</link>
            <wp:postmeta>
                <wp:meta_key>_thumbnail_id</wp:meta_key>
                <wp:meta_value>103</wp:meta_value>
            </wp:postmeta>
            <content:encoded><![CDATA[<p>Post body.</p>]]></content:encoded>
        </item>
        <item>
            <title>Image</title>
            <wp:post_id>103</wp:post_id>
            <wp:post_type>attachment</wp:post_type>
            <wp:attachment_url>https://example.com/wp-content/uploads/2025/10/image.jpg</wp:attachment_url>
        </item>
        <item>
            <title>Custom Product</title>
            <wp:post_id>104</wp:post_id>
            <wp:post_type>product</wp:post_type>
        </item>
    </channel>
</rss>
XML;

    File::put($path, $xml);

    return new UploadedFile($path, 'wordpress-export.xml', 'text/xml', null, true);
}

it('redirects guests from import admin routes to login', function (): void {
    registerImportProvider();

    $this->get('/admin/import')->assertRedirect('/admin/login');
    $this->post('/admin/import/analyze')->assertRedirect('/admin/login');
    $this->post('/admin/import/start')->assertRedirect('/admin/login');
    $this->get('/admin/import/progress/test-run')->assertRedirect('/admin/login');
    $this->post('/admin/import/run')->assertRedirect('/admin/login');
    $this->post('/admin/import/run/stream')->assertRedirect('/admin/login');
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
        ->assertSessionHas('tp_notice_success', fn (string $message): bool => str_contains($message, 'Pages created: 1')
            && str_contains($message, 'Pages skipped: 0')
            && str_contains($message, 'Pages failed: 0'));

    expect(
        TpPage::query()->where('slug', 'imported-home')->exists()
    )->toBeTrue();
});

it('streams import progress updates for a super admin', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-stream@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeImportBundleWithSinglePage();

    $analyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);

    $token = (string) $analyzeResponse->viewData('token');

    $response = $this->actingAs($admin)
        ->post('/admin/import/run/stream', [
            'token' => $token,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '0',
            'include_media' => '0',
            'include_seo' => '0',
        ]);

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');
    expect($response->streamedContent())->toContain('"kind":"phase"');
    expect($response->streamedContent())->toContain('"event":"progress"');
    expect($response->streamedContent())->toContain('"event":"done"');
});

it('redirects analyze selections to a dedicated progress page', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-progress-route@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeImportBundleWithSinglePage();

    $analyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);

    $token = (string) $analyzeResponse->viewData('token');

    $startResponse = $this->actingAs($admin)
        ->post('/admin/import/start', [
            'token' => $token,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '0',
            'include_media' => '0',
            'include_seo' => '0',
        ]);

    $startResponse->assertRedirect();
    $location = (string) $startResponse->headers->get('Location');

    expect($location)->toContain('/admin/import/progress/');

    $this->actingAs($admin)
        ->get($location)
        ->assertOk()
        ->assertViewIs('tentapress-import::progress')
        ->assertSee('Import progress');
});

it('allows a super admin to analyze a wordpress wxr bundle', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-wxr@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeWxrBundleWithPagePostAndAttachment();

    $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ])
        ->assertOk()
        ->assertViewIs('tentapress-import::review')
        ->assertSee('Settings are not included in WordPress WXR imports.')
        ->assertViewHas('summary', fn (array $summary): bool => ($summary['pages'] ?? null) === 1
            && ($summary['posts'] ?? null) === 1
            && ($summary['media'] ?? null) === 1
            && ($summary['categories'] ?? null) === 1
            && ($summary['tags'] ?? null) === 1
            && ($summary['unsupported_items'] ?? null) === 1
            && ($summary['unsupported_types']['product'] ?? null) === 1
            && (($summary['url_mappings_preview'][0]['destination_url'] ?? null) === '/wxr-page-title')
            && (($summary['url_mappings_preview'][1]['destination_url'] ?? null) === '/blog/wxr-post-title')
            && ($summary['featured_image_refs'] ?? null) === 1
            && ($summary['featured_image_resolved'] ?? null) === 1)
        ->assertViewHas('meta', fn (array $meta): bool => ($meta['source_format'] ?? null) === 'wxr');
});

it('allows a super admin to analyze and run a wordpress wxr bundle', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-wxr-run@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeWxrBundleWithPagePostAndAttachment();
    Http::fake([
        'https://example.com/*' => Http::response('fake-image-content', 200, [
            'Content-Type' => 'image/jpeg',
        ]),
    ]);

    $analyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);

    $analyzeResponse
        ->assertOk()
        ->assertViewHas('token');

    $token = (string) $analyzeResponse->viewData('token');

    $this->actingAs($admin)
        ->post('/admin/import/run', [
            'token' => $token,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '1',
            'include_media' => '1',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHas('tp_notice_success', fn (string $message): bool => str_contains($message, 'Source: WordPress WXR')
            && str_contains($message, 'Pages skipped: 0')
            && str_contains($message, 'Pages failed: 0')
            && str_contains($message, 'Posts skipped: 0')
            && str_contains($message, 'Posts failed: 0')
            && str_contains($message, 'Media skipped: 0')
            && str_contains($message, 'Media failed: 0')
            && str_contains($message, 'Media files copied: 1')
            && str_contains($message, 'Media variants refreshed: 1')
            && str_contains($message, 'URL mapping report: storage/app/tp-import-reports/')
            && str_contains($message, 'Reference map report: storage/app/tp-import-reports/'));

    expect(TpPage::query()->where('slug', 'wxr-page-title')->exists())->toBeTrue();
    expect(TpPost::query()->where('slug', 'wxr-post-title')->exists())->toBeTrue();
    $mediaPath = (string) (TpMedia::query()->latest('id')->value('path') ?? '');
    expect(Str::startsWith($mediaPath, 'media/imports/wordpress/'))->toBeTrue();
    expect(Str::endsWith($mediaPath, '/image-103.jpg'))->toBeTrue();
    Storage::disk('public')->assertExists($mediaPath);
    expect((int) (TpPost::query()->where('slug', 'wxr-post-title')->value('author_id') ?? 0))->toBe((int) $admin->id);

    $reports = File::glob(storage_path('app/tp-import-reports/*.json'));
    expect(is_array($reports))->toBeTrue();
    expect(count($reports))->toBeGreaterThan(0);

    $latestReportPath = is_array($reports) && $reports !== [] ? (string) end($reports) : '';
    expect($latestReportPath)->not->toBe('');

    $report = json_decode((string) File::get($latestReportPath), true, flags: JSON_THROW_ON_ERROR);
    expect(is_array($report))->toBeTrue();
    expect($report['source_format'] ?? null)->toBe('wxr');
    expect(is_array($report['mappings'] ?? null))->toBeTrue();
    expect(count($report['mappings'] ?? []))->toBeGreaterThanOrEqual(2);

    $referenceReports = File::glob(storage_path('app/tp-import-reports/*-references.json'));
    expect(is_array($referenceReports))->toBeTrue();
    expect(count($referenceReports))->toBeGreaterThan(0);

    $latestReferenceReportPath = is_array($referenceReports) && $referenceReports !== [] ? (string) end($referenceReports) : '';
    expect($latestReferenceReportPath)->not->toBe('');

    $referenceReport = json_decode((string) File::get($latestReferenceReportPath), true, flags: JSON_THROW_ON_ERROR);
    expect(is_array($referenceReport))->toBeTrue();
    expect($referenceReport['source_format'] ?? null)->toBe('wxr');
    expect(is_array($referenceReport['pages'] ?? null))->toBeTrue();
    expect(is_array($referenceReport['posts'] ?? null))->toBeTrue();
    expect(is_array($referenceReport['media'] ?? null))->toBeTrue();

    $firstPageReference = is_array($referenceReport['pages'] ?? null) && $referenceReport['pages'] !== [] ? $referenceReport['pages'][0] : [];
    $firstPostReference = is_array($referenceReport['posts'] ?? null) && $referenceReport['posts'] !== [] ? $referenceReport['posts'][0] : [];
    $firstMediaReference = is_array($referenceReport['media'] ?? null) && $referenceReport['media'] !== [] ? $referenceReport['media'][0] : [];

    expect((string) ($firstPageReference['source_post_id'] ?? ''))->toBe('101');
    expect((string) ($firstPageReference['destination_slug'] ?? ''))->toBe('wxr-page-title');
    expect((string) ($firstPostReference['source_post_id'] ?? ''))->toBe('102');
    expect((string) ($firstPostReference['destination_slug'] ?? ''))->toBe('wxr-post-title');
    expect((string) ($firstMediaReference['source_post_id'] ?? ''))->toBe('103');
    expect((string) ($firstMediaReference['destination_path'] ?? ''))->toContain('/image-103.jpg');
});

it('skips duplicate wxr source rows on create-only rerun', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-wxr-rerun@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeWxrBundleWithPagePostAndAttachment();
    Http::fake([
        'https://example.com/*' => Http::response('fake-image-content', 200, [
            'Content-Type' => 'image/jpeg',
        ]),
    ]);

    $firstAnalyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $bundle,
        ]);
    $firstToken = (string) $firstAnalyzeResponse->viewData('token');

    $this->actingAs($admin)
        ->post('/admin/import/run', [
            'token' => $firstToken,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '1',
            'include_media' => '1',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import');

    $secondBundle = makeWxrBundleWithPagePostAndAttachment();

    $secondAnalyzeResponse = $this->actingAs($admin)
        ->post('/admin/import/analyze', [
            'bundle' => $secondBundle,
        ]);
    $secondToken = (string) $secondAnalyzeResponse->viewData('token');

    $this->actingAs($admin)
        ->post('/admin/import/run', [
            'token' => $secondToken,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '1',
            'include_media' => '1',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHas('tp_notice_success', fn (string $message): bool => str_contains($message, 'Pages created: 0')
            && str_contains($message, 'Pages skipped: 1')
            && str_contains($message, 'Pages failed: 0')
            && str_contains($message, 'Posts created: 0')
            && str_contains($message, 'Posts skipped: 1')
            && str_contains($message, 'Posts failed: 0')
            && str_contains($message, 'Media failed: 0'));

    expect(TpPage::query()->where('slug', 'wxr-page-title')->count())->toBe(1);
    expect(TpPage::query()->where('slug', 'wxr-page-title-2')->exists())->toBeFalse();
    expect(TpPost::query()->where('slug', 'wxr-post-title')->count())->toBe(1);
    expect(TpPost::query()->where('slug', 'wxr-post-title-2')->exists())->toBeFalse();
});

it('allows rerunning import with the same token without token-expired errors', function (): void {
    registerImportProvider();

    $admin = TpUser::query()->create([
        'name' => 'Import Admin',
        'email' => 'import-same-token-rerun@example.test',
        'password' => 'secret',
        'is_super_admin' => true,
    ]);

    $bundle = makeWxrBundleWithPagePostAndAttachment();
    Http::fake([
        'https://example.com/*' => Http::response('fake-image-content', 200, [
            'Content-Type' => 'image/jpeg',
        ]),
    ]);

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
            'include_posts' => '1',
            'include_media' => '1',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHas('tp_notice_success');

    $this->actingAs($admin)
        ->post('/admin/import/run', [
            'token' => $token,
            'pages_mode' => 'create_only',
            'settings_mode' => 'merge',
            'include_posts' => '1',
            'include_media' => '1',
            'include_seo' => '0',
        ])
        ->assertRedirect('/admin/import')
        ->assertSessionHas('tp_notice_success', fn (string $message): bool => str_contains($message, 'Pages skipped: 1')
            && str_contains($message, 'Posts skipped: 1')
            && str_contains($message, 'Media skipped: 1')
            && str_contains($message, 'Media variants refreshed: 0'));
});
