<?php

declare(strict_types=1);

namespace TentaPress\Import\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Support\JsonPayload;
use ZipArchive;

final readonly class Importer
{
    public function __construct(private JsonPayload $jsonPayload)
    {
    }

    /**
     * Analyze the uploaded bundle, extract relevant payload into storage, return summary + token.
     *
     * @return array{
     *   token:string,
     *   summary:array<string,mixed>,
     *   meta:array<string,mixed>
     * }
     */
    public function analyzeBundle(UploadedFile $bundleFile): array
    {
        if ($this->isZipBundle($bundleFile)) {
            return $this->analyzeZipBundle($bundleFile);
        }

        return $this->analyzeWxrBundle($bundleFile);
    }

    /**
     * @return array{
     *   token:string,
     *   summary:array<string,mixed>,
     *   meta:array<string,mixed>
     * }
     */
    private function analyzeZipBundle(UploadedFile $zipFile): array
    {
        $token = $this->token();

        $baseDir = storage_path('app/tp-imports/' . $token);
        File::ensureDirectoryExists($baseDir);

        $zipPath = $baseDir . DIRECTORY_SEPARATOR . 'bundle.zip';
        $zipFile->move($baseDir, 'bundle.zip');

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);
        throw_if($opened !== true, \RuntimeException::class, 'Unable to open zip file.');

        $manifest = $this->readJsonFromZip($zip, 'manifest.json');
        if ($manifest === null) {
            $zip->close();
            throw new \RuntimeException('manifest.json not found in zip.');
        }

        $schema = (int) ($manifest['schema_version'] ?? 0);
        if ($schema !== 1) {
            $zip->close();
            throw new \RuntimeException('Unsupported export schema_version.');
        }

        $pages = $this->readJsonFromZip($zip, 'pages.json') ?? null;
        $posts = $this->readJsonFromZip($zip, 'posts.json') ?? null;
        $media = $this->readJsonFromZip($zip, 'media.json') ?? null;
        $settings = $this->readJsonFromZip($zip, 'settings.json') ?? null;
        $theme = $this->readJsonFromZip($zip, 'theme.json') ?? null;
        $plugins = $this->readJsonFromZip($zip, 'plugins.json') ?? null;
        $seo = $this->readJsonFromZip($zip, 'seo.json') ?? null;

        $zip->close();

        // Persist extracted payloads for the "run" step
        $plan = [
            'schema_version' => 1,
            'generated_at_utc' => (string) ($manifest['generated_at_utc'] ?? ''),
            'includes' => is_array($manifest['includes'] ?? null) ? $manifest['includes'] : [],
            'files' => [
                'pages' => $pages !== null,
                'posts' => $posts !== null,
                'media' => $media !== null,
                'settings' => $settings !== null,
                'theme' => $theme !== null,
                'plugins' => $plugins !== null,
                'seo' => $seo !== null,
            ],
        ];

        File::put($baseDir . DIRECTORY_SEPARATOR . 'plan.json', $this->jsonPayload->encode($plan));

        if ($pages !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'pages.json', $this->jsonPayload->encode($pages));
        }
        if ($posts !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'posts.json', $this->jsonPayload->encode($posts));
        }
        if ($media !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'media.json', $this->jsonPayload->encode($media));
        }
        if ($settings !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'settings.json', $this->jsonPayload->encode($settings));
        }
        if ($theme !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'theme.json', $this->jsonPayload->encode($theme));
        }
        if ($plugins !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'plugins.json', $this->jsonPayload->encode($plugins));
        }
        if ($seo !== null) {
            File::put($baseDir . DIRECTORY_SEPARATOR . 'seo.json', $this->jsonPayload->encode($seo));
        }

        $summary = [
            'pages' => $this->countItems($pages),
            'posts' => $this->countItems($posts),
            'media' => $this->countItems($media),
            'settings' => $this->countItems($settings),
            'seo' => $this->countSeoItems($seo),
            'theme_active_id' => is_array($theme) ? (string) ($theme['active_theme_id'] ?? '') : '',
            'enabled_plugins' => is_array($plugins) ? (int) count(($plugins['enabled'] ?? []) ?: []) : 0,
        ];

        $meta = [
            'schema_version' => 1,
            'generated_at_utc' => (string) ($manifest['generated_at_utc'] ?? ''),
        ];

        return [
            'token' => $token,
            'summary' => $summary,
            'meta' => $meta,
        ];
    }

    /**
     * @return array{
     *   token:string,
     *   summary:array<string,mixed>,
     *   meta:array<string,mixed>
     * }
     */
    private function analyzeWxrBundle(UploadedFile $wxrFile): array
    {
        $token = $this->token();

        $baseDir = storage_path('app/tp-imports/' . $token);
        File::ensureDirectoryExists($baseDir);

        $xmlPath = $baseDir . DIRECTORY_SEPARATOR . 'bundle.xml';
        $wxrFile->move($baseDir, 'bundle.xml');

        $xmlRaw = File::get($xmlPath);
        throw_if(trim((string) $xmlRaw) === '', \RuntimeException::class, 'WXR file is empty.');

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string((string) $xmlRaw, SimpleXMLElement::class, LIBXML_NOCDATA);
        throw_if($xml === false, \RuntimeException::class, 'Invalid WXR XML.');

        $namespaces = $xml->getDocNamespaces(true);
        foreach ($namespaces as $prefix => $namespaceUri) {
            if ($prefix === '' || !is_string($namespaceUri) || $namespaceUri === '') {
                continue;
            }

            $xml->registerXPathNamespace($prefix, $namespaceUri);
        }

        $wxrVersion = trim((string) ($xml->xpath('/rss/channel/wp:wxr_version')[0] ?? ''));

        $pagesItems = [];
        $postsItems = [];
        $mediaItems = [];
        $unsupportedByType = [];

        $items = $xml->xpath('/rss/channel/item');
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!$item instanceof SimpleXMLElement) {
                    continue;
                }

                foreach ($namespaces as $prefix => $namespaceUri) {
                    if ($prefix === '' || !is_string($namespaceUri) || $namespaceUri === '') {
                        continue;
                    }

                    $item->registerXPathNamespace($prefix, $namespaceUri);
                }

                $postType = trim((string) ($item->xpath('wp:post_type')[0] ?? ''));

                $title = trim((string) ($item->title ?? ''));
                $contentEncoded = trim((string) ($item->xpath('content:encoded')[0] ?? ''));
                $plainContent = $this->plainContentFromHtml($contentEncoded);
                $slugRaw = trim((string) ($item->xpath('wp:post_name')[0] ?? ''));
                $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($title);
                if ($slug === '') {
                    $slug = 'imported-' . substr($this->token(), 0, 8);
                }

                if ($postType === 'page') {
                    $pagesItems[] = [
                        'title' => $title,
                        'slug' => $slug,
                        'status' => $this->normalizeWxrStatus((string) ($item->xpath('wp:status')[0] ?? '')),
                        'layout' => 'default',
                        'blocks' => $this->contentBlocks($plainContent),
                    ];

                    continue;
                }

                if ($postType === 'post') {
                    $postsItems[] = [
                        'title' => $title,
                        'slug' => $slug,
                        'status' => $this->normalizeWxrStatus((string) ($item->xpath('wp:status')[0] ?? '')),
                        'layout' => 'default',
                        'blocks' => $this->contentBlocks($plainContent),
                        'published_at' => $this->normalizeWxrDate(
                            (string) ($item->xpath('wp:post_date_gmt')[0] ?? ''),
                            (string) ($item->xpath('wp:post_date')[0] ?? ''),
                        ),
                        'author_id' => null,
                    ];

                    continue;
                }

                if ($postType === 'attachment') {
                    $attachmentUrl = trim((string) ($item->xpath('wp:attachment_url')[0] ?? ''));
                    $path = ltrim((string) parse_url($attachmentUrl, PHP_URL_PATH), '/');

                    if ($path === '') {
                        continue;
                    }

                    $mediaItems[] = [
                        'path' => $path,
                        'disk' => 'public',
                        'title' => $title !== '' ? $title : null,
                        'mime_type' => $this->mimeFromAttachmentPath($path),
                    ];

                    continue;
                }

                if ($postType !== '') {
                    $unsupportedByType[$postType] = (int) ($unsupportedByType[$postType] ?? 0) + 1;
                }
            }
        }

        $categories = $xml->xpath('/rss/channel/wp:category');
        $tags = $xml->xpath('/rss/channel/wp:tag');

        $generatedAtUtc = now()->toIso8601String();
        $plan = [
            'source_format' => 'wxr',
            'schema_version' => 1,
            'generated_at_utc' => $generatedAtUtc,
            'wxr_version' => $wxrVersion,
            'files' => [
                'pages' => $pagesItems !== [],
                'posts' => $postsItems !== [],
                'media' => $mediaItems !== [],
                'settings' => false,
                'theme' => false,
                'plugins' => false,
                'seo' => false,
            ],
        ];

        File::put($baseDir . DIRECTORY_SEPARATOR . 'plan.json', $this->jsonPayload->encode($plan));
        File::put($baseDir . DIRECTORY_SEPARATOR . 'wxr.xml', (string) $xmlRaw);
        File::put($baseDir . DIRECTORY_SEPARATOR . 'pages.json', $this->jsonPayload->encode([
            'count' => count($pagesItems),
            'items' => $pagesItems,
        ]));
        File::put($baseDir . DIRECTORY_SEPARATOR . 'posts.json', $this->jsonPayload->encode([
            'count' => count($postsItems),
            'items' => $postsItems,
        ]));
        File::put($baseDir . DIRECTORY_SEPARATOR . 'media.json', $this->jsonPayload->encode([
            'count' => count($mediaItems),
            'items' => $mediaItems,
        ]));

        return [
            'token' => $token,
            'summary' => [
                'pages' => count($pagesItems),
                'posts' => count($postsItems),
                'media' => count($mediaItems),
                'settings' => 0,
                'seo' => 0,
                'categories' => is_array($categories) ? count($categories) : 0,
                'tags' => is_array($tags) ? count($tags) : 0,
                'unsupported_items' => array_sum($unsupportedByType),
                'unsupported_types' => $unsupportedByType,
                'theme_active_id' => '',
                'enabled_plugins' => 0,
            ],
            'meta' => [
                'schema_version' => 1,
                'source_format' => 'wxr',
                'wxr_version' => $wxrVersion !== '' ? $wxrVersion : 'unknown',
                'generated_at_utc' => $generatedAtUtc,
            ],
        ];
    }

    private function isZipBundle(UploadedFile $bundleFile): bool
    {
        $extension = strtolower((string) $bundleFile->getClientOriginalExtension());
        if ($extension === 'zip') {
            return true;
        }

        $mimeType = strtolower((string) $bundleFile->getClientMimeType());

        return str_contains($mimeType, 'zip');
    }

    private function normalizeWxrStatus(string $status): string
    {
        return trim(strtolower($status)) === 'publish' ? 'published' : 'draft';
    }

    private function normalizeWxrDate(string $gmt, string $local): ?string
    {
        $candidate = trim($gmt) !== '' && trim($gmt) !== '0000-00-00 00:00:00' ? trim($gmt) : trim($local);
        if ($candidate === '' || $candidate === '0000-00-00 00:00:00') {
            return null;
        }

        return str_replace(' ', 'T', $candidate);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function contentBlocks(string $plainContent): array
    {
        if ($plainContent === '') {
            return [];
        }

        return [
            [
                'type' => 'blocks/content',
                'props' => [
                    'content' => $plainContent,
                    'width' => 'normal',
                    'alignment' => 'left',
                    'background' => 'none',
                ],
            ],
        ];
    }

    private function plainContentFromHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $withNewlines = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        if (!is_string($withNewlines)) {
            return '';
        }

        $stripped = strip_tags($withNewlines);
        $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", $decoded) ?? $decoded);
    }

    private function mimeFromAttachmentPath(string $path): ?string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'avif' => 'image/avif',
            default => null,
        };
    }

    /**
     * Run the import using an analysis token.
     *
     * @param array{
     *   pages_mode:string,
     *   settings_mode:string,
     *   include_posts?:bool,
     *   include_media?:bool,
     *   include_seo?:bool
     * } $options
     *
     * @return array{message:string}
     */
    public function runImport(string $token, array $options = []): array
    {
        $token = trim($token);
        throw_if($token === '', \RuntimeException::class, 'Invalid token.');

        $baseDir = storage_path('app/tp-imports/' . $token);
        $planPath = $baseDir . DIRECTORY_SEPARATOR . 'plan.json';

        throw_if(!is_dir($baseDir) || !is_file($planPath), \RuntimeException::class, 'Import token not found or expired.');

        $pagesMode = (string) ($options['pages_mode'] ?? 'create_only');
        $settingsMode = (string) ($options['settings_mode'] ?? 'merge');
        $includePosts = (bool) ($options['include_posts'] ?? true);
        $includeMedia = (bool) ($options['include_media'] ?? true);
        $includeSeo = (bool) ($options['include_seo'] ?? false);

        $createdPages = 0;
        $createdPosts = 0;
        $createdMedia = 0;
        $createdSettings = 0;
        $updatedSettings = 0;
        $importedSeo = 0;

        DB::beginTransaction();

        try {
            // Pages
            $pagesPath = $baseDir . DIRECTORY_SEPARATOR . 'pages.json';
            if (is_file($pagesPath)) {
                $pagesPayload = $this->readJsonFile($pagesPath);
                [$createdPages] = $this->importPages($pagesPayload, $pagesMode);
            }

            if ($includePosts) {
                $postsPath = $baseDir . DIRECTORY_SEPARATOR . 'posts.json';
                if (is_file($postsPath)) {
                    $postsPayload = $this->readJsonFile($postsPath);
                    [$createdPosts] = $this->importPosts($postsPayload);
                }
            }

            if ($includeMedia) {
                $mediaPath = $baseDir . DIRECTORY_SEPARATOR . 'media.json';
                if (is_file($mediaPath)) {
                    $mediaPayload = $this->readJsonFile($mediaPath);
                    [$createdMedia] = $this->importMedia($mediaPayload);
                }
            }

            // Settings
            $settingsPath = $baseDir . DIRECTORY_SEPARATOR . 'settings.json';
            if (is_file($settingsPath)) {
                $settingsPayload = $this->readJsonFile($settingsPath);
                [$createdSettings, $updatedSettings] = $this->importSettings($settingsPayload, $settingsMode);
            }

            // SEO (optional)
            if ($includeSeo) {
                $seoPath = $baseDir . DIRECTORY_SEPARATOR . 'seo.json';
                if (is_file($seoPath)) {
                    $seoPayload = $this->readJsonFile($seoPath);
                    $importedSeo = $this->importSeo($seoPayload);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            // Clean up after run (keeps installs tidy)
            $this->cleanup($baseDir);
        }

        $parts = [];
        if ($createdPages > 0) {
            $parts[] = "Pages created: {$createdPages}";
        } else {
            $parts[] = "Pages created: 0";
        }

        if ($includePosts) {
            $parts[] = "Posts created: {$createdPosts}";
        }

        if ($includeMedia) {
            $parts[] = "Media created: {$createdMedia}";
        }

        $parts[] = "Settings created: {$createdSettings}";
        $parts[] = "Settings updated: {$updatedSettings}";

        if ($includeSeo) {
            $parts[] = "SEO rows imported: {$importedSeo}";
        }

        return [
            'message' => 'Import completed. ' . implode(' · ', $parts),
        ];
    }

    /**
     * @return array{0:int,1:int} createdPages, skippedPages
     */
    private function importPages(array $payload, string $mode): array
    {
        if ($mode !== 'create_only') {
            $mode = 'create_only';
        }

        if (!class_exists(TpPage::class)) {
            return [0, 0];
        }

        if (!Schema::hasTable('tp_pages')) {
            return [0, 0];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0];
        }

        $hasStatus = Schema::hasColumn('tp_pages', 'status');
        $hasLayout = Schema::hasColumn('tp_pages', 'layout');
        $hasBlocks = Schema::hasColumn('tp_pages', 'blocks');

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($slug === '') {
                $skipped++;
                continue;
            }

            $slug = $this->uniqueSlug($slug);

            $data = [
                'title' => $title,
                'slug' => $slug,
            ];

            if ($hasLayout) {
                $layout = trim((string) ($item['layout'] ?? ''));
                $data['layout'] = $layout !== '' ? $layout : 'default';
            }

            if ($hasStatus) {
                $status = trim((string) ($item['status'] ?? ''));
                $data['status'] = $status !== '' ? $status : 'published';
            }

            if ($hasBlocks) {
                $blocks = $item['blocks'] ?? [];
                $data['blocks'] = is_array($blocks) ? $blocks : [];
            }

            $model = TpPage::query()->create($data);
            unset($model);

            $created++;
        }

        return [$created, $skipped];
    }

    private function uniqueSlug(string $slug): string
    {
        $base = Str::slug($slug, '-');
        if ($base === '') {
            $base = $slug;
        }

        $candidate = $base;
        $i = 2;

        while (DB::table('tp_pages')->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 5000) {
                // safety
                break;
            }
        }

        return $candidate;
    }

    /**
     * @return array{0:int,1:int} createdPosts, skippedPosts
     */
    private function importPosts(array $payload): array
    {
        if (!class_exists(TpPost::class)) {
            return [0, 0];
        }

        if (!Schema::hasTable('tp_posts')) {
            return [0, 0];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0];
        }

        $hasStatus = Schema::hasColumn('tp_posts', 'status');
        $hasLayout = Schema::hasColumn('tp_posts', 'layout');
        $hasBlocks = Schema::hasColumn('tp_posts', 'blocks');
        $hasPublishedAt = Schema::hasColumn('tp_posts', 'published_at');
        $hasAuthor = Schema::hasColumn('tp_posts', 'author_id');
        $hasUsers = Schema::hasTable('tp_users');

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($slug === '') {
                $skipped++;
                continue;
            }

            $slug = $this->uniquePostSlug($slug);

            $data = [
                'title' => $title,
                'slug' => $slug,
            ];

            if ($hasLayout) {
                $layout = trim((string) ($item['layout'] ?? ''));
                $data['layout'] = $layout !== '' ? $layout : 'default';
            }

            if ($hasStatus) {
                $status = trim((string) ($item['status'] ?? ''));
                $data['status'] = $status !== '' ? $status : 'draft';
            }

            if ($hasBlocks) {
                $blocks = $item['blocks'] ?? [];
                $data['blocks'] = is_array($blocks) ? $blocks : [];
            }

            if ($hasPublishedAt) {
                $publishedAt = $item['published_at'] ?? null;
                $data['published_at'] = $publishedAt === null || $publishedAt === '' ? null : (string) $publishedAt;
            }

            if ($hasAuthor) {
                $authorId = (int) ($item['author_id'] ?? 0);
                if ($authorId > 0 && $hasUsers && DB::table('tp_users')->where('id', $authorId)->exists()) {
                    $data['author_id'] = $authorId;
                } else {
                    $data['author_id'] = null;
                }
            }

            $model = TpPost::query()->create($data);
            unset($model);

            $created++;
        }

        return [$created, $skipped];
    }

    private function uniquePostSlug(string $slug): string
    {
        $base = Str::slug($slug, '-');
        if ($base === '') {
            $base = $slug;
        }

        $candidate = $base;
        $i = 2;

        while (DB::table('tp_posts')->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 5000) {
                break;
            }
        }

        return $candidate;
    }

    /**
     * @return array{0:int,1:int} createdMedia, skippedMedia
     */
    private function importMedia(array $payload): array
    {
        if (!class_exists(TpMedia::class)) {
            return [0, 0];
        }

        if (!Schema::hasTable('tp_media')) {
            return [0, 0];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0];
        }

        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $path = trim((string) ($item['path'] ?? ''));
            if ($path === '') {
                $skipped++;
                continue;
            }

            if (DB::table('tp_media')->where('path', $path)->exists()) {
                $skipped++;
                continue;
            }

            $data = [
                'path' => $path,
                'disk' => (string) ($item['disk'] ?? 'public'),
                'title' => array_key_exists('title', $item) ? ($item['title'] === null ? null : (string) $item['title']) : null,
                'alt_text' => array_key_exists('alt_text', $item) ? ($item['alt_text'] === null ? null : (string) $item['alt_text']) : null,
                'caption' => array_key_exists('caption', $item) ? ($item['caption'] === null ? null : (string) $item['caption']) : null,
                'original_name' => array_key_exists('original_name', $item)
                    ? ($item['original_name'] === null ? null : (string) $item['original_name'])
                    : null,
                'mime_type' => array_key_exists('mime_type', $item) ? ($item['mime_type'] === null ? null : (string) $item['mime_type']) : null,
                'size' => array_key_exists('size', $item) ? ($item['size'] === null ? null : (int) $item['size']) : null,
                'width' => array_key_exists('width', $item) ? ($item['width'] === null ? null : (int) $item['width']) : null,
                'height' => array_key_exists('height', $item) ? ($item['height'] === null ? null : (int) $item['height']) : null,
            ];

            $model = TpMedia::query()->create($data);
            unset($model);

            $created++;
        }

        return [$created, $skipped];
    }

    /**
     * @return array{0:int,1:int} created, updated
     */
    private function importSettings(array $payload, string $mode): array
    {
        if (!Schema::hasTable('tp_settings')) {
            return [0, 0];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0];
        }

        $mode = $mode === 'overwrite' ? 'overwrite' : 'merge';

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $key = trim((string) ($item['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $value = array_key_exists('value', $item) ? ($item['value'] === null ? null : (string) $item['value']) : null;
            $autoload = (bool) ($item['autoload'] ?? true);

            $exists = DB::table('tp_settings')->where('key', $key)->exists();

            if ($exists && $mode === 'merge') {
                continue;
            }

            if ($exists) {
                DB::table('tp_settings')->where('key', $key)->update([
                    'value' => $value,
                    'autoload' => $autoload,
                    'updated_at' => now(),
                ]);
                $updated++;
            } else {
                DB::table('tp_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'autoload' => $autoload,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }
        }

        return [$created, $updated];
    }

    private function importSeo(array $payload): int
    {
        $items = $this->seoItemsFromPayload($payload);
        $pageItems = $items['pages'];
        $postItems = $items['posts'];

        if ($pageItems === [] && $postItems === []) {
            return 0;
        }

        $imported = 0;

        if (Schema::hasTable('tp_seo_pages') && Schema::hasTable('tp_pages')) {
            // If pages were imported with new IDs, we currently have no mapping.
            // v0 behaviour: only import SEO rows whose page_id exists in this install.
            foreach ($pageItems as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $pageId = (int) ($item['page_id'] ?? 0);
                if ($pageId <= 0) {
                    continue;
                }

                if (!DB::table('tp_pages')->where('id', $pageId)->exists()) {
                    continue;
                }

                $data = [
                    'page_id' => $pageId,
                    'title' => array_key_exists('title', $item) ? ($item['title'] === null ? null : (string) $item['title']) : null,
                    'description' => array_key_exists('description', $item) ? ($item['description'] === null ? null : (string) $item['description']) : null,
                    'canonical_url' => array_key_exists('canonical_url', $item) ? ($item['canonical_url'] === null ? null : (string) $item['canonical_url']) : null,
                    'robots' => array_key_exists('robots', $item) ? ($item['robots'] === null ? null : (string) $item['robots']) : null,
                    'og_title' => array_key_exists('og_title', $item) ? ($item['og_title'] === null ? null : (string) $item['og_title']) : null,
                    'og_description' => array_key_exists('og_description', $item) ? ($item['og_description'] === null ? null : (string) $item['og_description']) : null,
                    'og_image' => array_key_exists('og_image', $item) ? ($item['og_image'] === null ? null : (string) $item['og_image']) : null,
                    'twitter_title' => array_key_exists('twitter_title', $item) ? ($item['twitter_title'] === null ? null : (string) $item['twitter_title']) : null,
                    'twitter_description' => array_key_exists('twitter_description', $item) ? ($item['twitter_description'] === null ? null : (string) $item['twitter_description']) : null,
                    'twitter_image' => array_key_exists('twitter_image', $item) ? ($item['twitter_image'] === null ? null : (string) $item['twitter_image']) : null,
                    'updated_at' => now(),
                ];

                $exists = DB::table('tp_seo_pages')->where('page_id', $pageId)->exists();

                if ($exists) {
                    DB::table('tp_seo_pages')->where('page_id', $pageId)->update($data);
                } else {
                    $data['created_at'] = now();
                    DB::table('tp_seo_pages')->insert($data);
                }

                $imported++;
            }
        }

        if (Schema::hasTable('tp_seo_posts') && Schema::hasTable('tp_posts')) {
            foreach ($postItems as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $postId = (int) ($item['post_id'] ?? 0);
                if ($postId <= 0) {
                    continue;
                }

                if (!DB::table('tp_posts')->where('id', $postId)->exists()) {
                    continue;
                }

                $data = [
                    'post_id' => $postId,
                    'title' => array_key_exists('title', $item) ? ($item['title'] === null ? null : (string) $item['title']) : null,
                    'description' => array_key_exists('description', $item) ? ($item['description'] === null ? null : (string) $item['description']) : null,
                    'canonical_url' => array_key_exists('canonical_url', $item) ? ($item['canonical_url'] === null ? null : (string) $item['canonical_url']) : null,
                    'robots' => array_key_exists('robots', $item) ? ($item['robots'] === null ? null : (string) $item['robots']) : null,
                    'og_title' => array_key_exists('og_title', $item) ? ($item['og_title'] === null ? null : (string) $item['og_title']) : null,
                    'og_description' => array_key_exists('og_description', $item) ? ($item['og_description'] === null ? null : (string) $item['og_description']) : null,
                    'og_image' => array_key_exists('og_image', $item) ? ($item['og_image'] === null ? null : (string) $item['og_image']) : null,
                    'twitter_title' => array_key_exists('twitter_title', $item) ? ($item['twitter_title'] === null ? null : (string) $item['twitter_title']) : null,
                    'twitter_description' => array_key_exists('twitter_description', $item) ? ($item['twitter_description'] === null ? null : (string) $item['twitter_description']) : null,
                    'twitter_image' => array_key_exists('twitter_image', $item) ? ($item['twitter_image'] === null ? null : (string) $item['twitter_image']) : null,
                    'updated_at' => now(),
                ];

                $exists = DB::table('tp_seo_posts')->where('post_id', $postId)->exists();

                if ($exists) {
                    DB::table('tp_seo_posts')->where('post_id', $postId)->update($data);
                } else {
                    $data['created_at'] = now();
                    DB::table('tp_seo_posts')->insert($data);
                }

                $imported++;
            }
        }

        return $imported;
    }

    private function cleanup(string $dir): void
    {
        try {
            File::deleteDirectory($dir);
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @return array<int,mixed>
     */
    private function itemsFromPayload(array $payload): array
    {
        $items = $payload['items'] ?? [];
        return is_array($items) ? array_values($items) : [];
    }

    private function countItems(?array $payload): int
    {
        if (!is_array($payload)) {
            return 0;
        }
        $items = $payload['items'] ?? [];
        return is_array($items) ? count($items) : 0;
    }

    private function countSeoItems(?array $payload): int
    {
        if (!is_array($payload)) {
            return 0;
        }

        $items = $this->seoItemsFromPayload($payload);

        return count($items['pages']) + count($items['posts']);
    }

    /**
     * @return array{pages:array<int,mixed>,posts:array<int,mixed>}
     */
    private function seoItemsFromPayload(array $payload): array
    {
        $pages = $payload['pages']['items'] ?? null;
        $posts = $payload['posts']['items'] ?? null;

        if (is_array($pages) || is_array($posts)) {
            return [
                'pages' => is_array($pages) ? array_values($pages) : [],
                'posts' => is_array($posts) ? array_values($posts) : [],
            ];
        }

        return [
            'pages' => $this->itemsFromPayload($payload),
            'posts' => [],
        ];
    }

    /**
     * @return array<string,mixed>|null
     */
    private function readJsonFromZip(ZipArchive $zip, string $path): ?array
    {
        $idx = $zip->locateName($path, ZipArchive::FL_NOCASE | ZipArchive::FL_NODIR);
        if ($idx === false) {
            return null;
        }

        $raw = $zip->getFromIndex($idx);
        if (!is_string($raw)) {
            return null;
        }

        return $this->jsonPayload->decode($raw);
    }

    /**
     * @return array<string,mixed>
     */
    private function readJsonFile(string $path): array
    {
        $raw = File::get($path);

        return $this->jsonPayload->decodeOrEmpty((string) $raw);
    }

    private function token(): string
    {
        return bin2hex(random_bytes(16));
    }
}
