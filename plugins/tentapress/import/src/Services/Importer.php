<?php

declare(strict_types=1);

namespace TentaPress\Import\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
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
        if ($xml === false) {
            throw new \RuntimeException($this->wxrXmlErrorMessage());
        }

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
        $unsupportedSamples = [];
        $categories = [];
        $tags = [];
        $urlMappingsPreview = [];
        $featuredImageRefs = [];
        $attachmentSourceIds = [];

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
                $sourcePostId = trim((string) ($item->xpath('wp:post_id')[0] ?? ''));
                $sourceLink = trim((string) ($item->link ?? ''));
                if ($slug === '') {
                    $slug = 'imported-' . substr($this->token(), 0, 8);
                }

                $itemCategories = $item->xpath('category');
                if (is_array($itemCategories)) {
                    foreach ($itemCategories as $itemCategory) {
                        if (!$itemCategory instanceof SimpleXMLElement) {
                            continue;
                        }

                        $domain = trim((string) ($itemCategory->attributes()->domain ?? ''));
                        $label = trim((string) $itemCategory);
                        if ($label === '') {
                            continue;
                        }

                        if ($domain === 'category') {
                            $categories[$label] = true;
                        }

                        if ($domain === 'post_tag') {
                            $tags[$label] = true;
                        }
                    }
                }

                if ($postType === 'page') {
                    $featuredImageSourceId = $this->featuredImageSourceId($item);
                    if ($featuredImageSourceId !== null) {
                        $featuredImageRefs[] = $featuredImageSourceId;
                    }

                    $pagesItems[] = [
                        'source_post_id' => $sourcePostId !== '' ? $sourcePostId : null,
                        'source_link' => $sourceLink !== '' ? $sourceLink : null,
                        'title' => $title,
                        'slug' => $slug,
                        'status' => $this->normalizeWxrStatus((string) ($item->xpath('wp:status')[0] ?? '')),
                        'layout' => 'default',
                        'blocks' => $this->contentBlocks($plainContent),
                    ];

                    if (count($urlMappingsPreview) < 25) {
                        $urlMappingsPreview[] = $this->urlMappingPreview('page', $sourceLink, $slug, $sourcePostId);
                    }

                    continue;
                }

                if ($postType === 'post') {
                    $featuredImageSourceId = $this->featuredImageSourceId($item);
                    if ($featuredImageSourceId !== null) {
                        $featuredImageRefs[] = $featuredImageSourceId;
                    }

                    $postsItems[] = [
                        'source_post_id' => $sourcePostId !== '' ? $sourcePostId : null,
                        'source_link' => $sourceLink !== '' ? $sourceLink : null,
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

                    if (count($urlMappingsPreview) < 25) {
                        $urlMappingsPreview[] = $this->urlMappingPreview('post', $sourceLink, $slug, $sourcePostId);
                    }

                    continue;
                }

                if ($postType === 'attachment') {
                    $attachmentUrl = trim((string) ($item->xpath('wp:attachment_url')[0] ?? ''));
                    $path = $this->mappedMediaPathFromAttachmentUrl($attachmentUrl, $sourcePostId);

                    if ($path === '') {
                        continue;
                    }

                    $mediaItems[] = [
                        'source_post_id' => $sourcePostId !== '' ? $sourcePostId : null,
                        'source_link' => $sourceLink !== '' ? $sourceLink : null,
                        'source_url' => $attachmentUrl !== '' ? $attachmentUrl : null,
                        'path' => $path,
                        'disk' => 'public',
                        'title' => $title !== '' ? $title : null,
                        'mime_type' => $this->mimeFromAttachmentPath($path),
                    ];

                    if ($sourcePostId !== '') {
                        $attachmentSourceIds[$sourcePostId] = true;
                    }

                    continue;
                }

                if ($postType !== '') {
                    $unsupportedByType[$postType] = (int) ($unsupportedByType[$postType] ?? 0) + 1;

                    if (count($unsupportedSamples) < 10) {
                        $unsupportedSamples[] = [
                            'type' => $postType,
                            'title' => $title,
                            'post_id' => $sourcePostId,
                            'link' => $sourceLink,
                        ];
                    }
                }
            }
        }

        $channelCategories = $xml->xpath('/rss/channel/wp:category');
        if (is_array($channelCategories)) {
            foreach ($channelCategories as $categoryNode) {
                if (!$categoryNode instanceof SimpleXMLElement) {
                    continue;
                }

                $label = trim((string) ($categoryNode->xpath('wp:cat_name')[0] ?? ''));
                if ($label !== '') {
                    $categories[$label] = true;
                }
            }
        }

        $channelTags = $xml->xpath('/rss/channel/wp:tag');
        if (is_array($channelTags)) {
            foreach ($channelTags as $tagNode) {
                if (!$tagNode instanceof SimpleXMLElement) {
                    continue;
                }

                $label = trim((string) ($tagNode->xpath('wp:tag_name')[0] ?? ''));
                if ($label !== '') {
                    $tags[$label] = true;
                }
            }
        }

        $generatedAtUtc = now()->toIso8601String();
        $plan = [
            'source_format' => 'wxr',
            'schema_version' => 1,
            'generated_at_utc' => $generatedAtUtc,
            'wxr_version' => $wxrVersion,
            'unsupported_items' => array_sum($unsupportedByType),
            'url_mappings_preview_count' => count($urlMappingsPreview),
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

        $featuredImageResolved = 0;
        foreach ($featuredImageRefs as $thumbnailId) {
            if (isset($attachmentSourceIds[$thumbnailId])) {
                $featuredImageResolved++;
            }
        }

        return [
            'token' => $token,
            'summary' => [
                'pages' => count($pagesItems),
                'posts' => count($postsItems),
                'media' => count($mediaItems),
                'settings' => 0,
                'seo' => 0,
                'categories' => count($categories),
                'tags' => count($tags),
                'unsupported_items' => array_sum($unsupportedByType),
                'unsupported_types' => $unsupportedByType,
                'unsupported_samples' => $unsupportedSamples,
                'url_mappings_preview' => $urlMappingsPreview,
                'featured_image_refs' => count($featuredImageRefs),
                'featured_image_resolved' => $featuredImageResolved,
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

    private function mappedMediaPathFromAttachmentUrl(string $attachmentUrl, string $sourcePostId): string
    {
        $parsedPath = (string) parse_url($attachmentUrl, PHP_URL_PATH);
        $basename = trim((string) pathinfo($parsedPath, PATHINFO_FILENAME));
        $extension = trim((string) pathinfo($parsedPath, PATHINFO_EXTENSION));

        $safeBase = Str::slug($basename);
        if ($safeBase === '') {
            $safeBase = 'attachment';
        }

        $safeSourceId = trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $sourcePostId) ?? '');
        $suffix = $safeSourceId !== '' ? '-' . $safeSourceId : '';
        $filename = $safeBase . $suffix . ($extension !== '' ? '.' . strtolower($extension) : '');

        return 'media/imports/wordpress/' . now()->format('Y/m') . '/' . $filename;
    }

    private function copyRemoteMediaToDisk(string $sourceUrl, string $disk, string $path): bool
    {
        $scheme = strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return false;
        }

        try {
            $storage = Storage::disk($disk);
            if ($storage->exists($path)) {
                return true;
            }

            $response = Http::timeout(20)->retry(2, 250)->get($sourceUrl);
            if (!$response->successful()) {
                return false;
            }

            $body = $response->body();
            if ($body === '') {
                return false;
            }

            $storage->put($path, $body);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function wxrXmlErrorMessage(): string
    {
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if ($errors === []) {
            return 'Invalid WXR XML.';
        }

        $first = $errors[0];
        $line = (int) ($first->line ?? 0);
        $message = trim((string) ($first->message ?? 'Invalid XML format.'));

        if ($line > 0) {
            return "Invalid WXR XML at line {$line}: {$message}";
        }

        return "Invalid WXR XML: {$message}";
    }

    /**
     * @return array{type:string,source_url:string,destination_url:string,source_post_id:string}
     */
    private function urlMappingPreview(string $type, string $sourceLink, string $slug, string $sourcePostId): array
    {
        $destinationUrl = $type === 'post'
            ? '/'.$this->blogBase().'/'.$slug
            : '/'.$slug;

        return [
            'type' => $type,
            'source_url' => $sourceLink,
            'destination_url' => $destinationUrl,
            'source_post_id' => $sourcePostId,
        ];
    }

    private function blogBase(): string
    {
        $defaultBase = 'blog';

        if (!class_exists(SettingsStore::class) || !app()->bound(SettingsStore::class)) {
            return $defaultBase;
        }

        $rawBase = trim((string) resolve(SettingsStore::class)->get('site.blog_base', ''), '/');
        if ($rawBase === '') {
            return $defaultBase;
        }

        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1 ? $rawBase : $defaultBase;
    }

    /**
     * @param  array<int,array<string,string|null>>  $pageMappings
     * @param  array<int,array<string,string|null>>  $postMappings
     */
    private function writeUrlMappingReport(string $token, array $pageMappings, array $postMappings): ?string
    {
        $mappings = array_values(array_filter(array_merge($pageMappings, $postMappings), fn (array $row): bool => trim((string) ($row['destination_url'] ?? '')) !== ''));

        if ($mappings === []) {
            return null;
        }

        $dir = storage_path('app/tp-import-reports');
        File::ensureDirectoryExists($dir);

        $path = $dir . DIRECTORY_SEPARATOR . $token . '.json';
        File::put($path, $this->jsonPayload->encode([
            'generated_at_utc' => now()->toIso8601String(),
            'source_format' => 'wxr',
            'mappings' => $mappings,
        ]));

        return 'storage/app/tp-import-reports/' . $token . '.json';
    }

    /**
     * @param  array<string,mixed>  $payload
     */
    private function emitProgress(?callable $progress, array $payload): void
    {
        if (!is_callable($progress)) {
            return;
        }

        $progress($payload);
    }

    private function featuredImageSourceId(SimpleXMLElement $item): ?string
    {
        $postMeta = $item->xpath('wp:postmeta');
        if (!is_array($postMeta)) {
            return null;
        }

        foreach ($postMeta as $metaNode) {
            if (!$metaNode instanceof SimpleXMLElement) {
                continue;
            }

            $metaKey = trim((string) ($metaNode->xpath('wp:meta_key')[0] ?? ''));
            if ($metaKey !== '_thumbnail_id') {
                continue;
            }

            $metaValue = trim((string) ($metaNode->xpath('wp:meta_value')[0] ?? ''));
            if ($metaValue !== '') {
                return $metaValue;
            }
        }

        return null;
    }

    /**
     * Run the import using an analysis token.
     *
     * @param array{
     *   pages_mode:string,
     *   settings_mode:string,
     *   include_posts?:bool,
     *   include_media?:bool,
     *   include_seo?:bool,
     *   actor_user_id?:int,
     *   progress?:callable(array<string,mixed>):void
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
        $plan = $this->readJsonFile($planPath);

        $pagesMode = (string) ($options['pages_mode'] ?? 'create_only');
        $settingsMode = (string) ($options['settings_mode'] ?? 'merge');
        $includePosts = (bool) ($options['include_posts'] ?? true);
        $includeMedia = (bool) ($options['include_media'] ?? true);
        $includeSeo = (bool) ($options['include_seo'] ?? false);
        $actorUserId = (int) ($options['actor_user_id'] ?? 0);
        $progress = is_callable($options['progress'] ?? null) ? $options['progress'] : null;

        $createdPages = 0;
        $createdPosts = 0;
        $createdMedia = 0;
        $downloadedMedia = 0;
        $createdSettings = 0;
        $updatedSettings = 0;
        $importedSeo = 0;
        $pageMappings = [];
        $postMappings = [];

        DB::beginTransaction();

        try {
            // Pages
            $pagesPath = $baseDir . DIRECTORY_SEPARATOR . 'pages.json';
            if (is_file($pagesPath)) {
                $this->emitProgress($progress, [
                    'kind' => 'phase',
                    'entity' => 'page',
                    'status' => 'started',
                ]);
                $pagesPayload = $this->readJsonFile($pagesPath);
                [$createdPages, , $pageMappings] = $this->importPages($pagesPayload, $pagesMode, $actorUserId, $progress);
                $this->emitProgress($progress, [
                    'kind' => 'phase',
                    'entity' => 'page',
                    'status' => 'completed',
                    'created' => $createdPages,
                ]);
            }

            if ($includePosts) {
                $postsPath = $baseDir . DIRECTORY_SEPARATOR . 'posts.json';
                if (is_file($postsPath)) {
                    $this->emitProgress($progress, [
                        'kind' => 'phase',
                        'entity' => 'post',
                        'status' => 'started',
                    ]);
                    $postsPayload = $this->readJsonFile($postsPath);
                    [$createdPosts, , $postMappings] = $this->importPosts($postsPayload, $actorUserId, $progress);
                    $this->emitProgress($progress, [
                        'kind' => 'phase',
                        'entity' => 'post',
                        'status' => 'completed',
                        'created' => $createdPosts,
                    ]);
                }
            }

            if ($includeMedia) {
                $mediaPath = $baseDir . DIRECTORY_SEPARATOR . 'media.json';
                if (is_file($mediaPath)) {
                    $this->emitProgress($progress, [
                        'kind' => 'phase',
                        'entity' => 'media',
                        'status' => 'started',
                    ]);
                    $mediaPayload = $this->readJsonFile($mediaPath);
                    [$createdMedia, , $downloadedMedia] = $this->importMedia($mediaPayload, $actorUserId, $progress);
                    $this->emitProgress($progress, [
                        'kind' => 'phase',
                        'entity' => 'media',
                        'status' => 'completed',
                        'created' => $createdMedia,
                        'copied' => $downloadedMedia,
                    ]);
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
            $parts[] = "Media files copied: {$downloadedMedia}";
        }

        $parts[] = "Settings created: {$createdSettings}";
        $parts[] = "Settings updated: {$updatedSettings}";

        if ($includeSeo) {
            $parts[] = "SEO rows imported: {$importedSeo}";
        }

        if (($plan['source_format'] ?? null) === 'wxr') {
            $parts[] = 'Source: WordPress WXR';
            $parts[] = 'Unsupported items skipped: '.(int) ($plan['unsupported_items'] ?? 0);
            $parts[] = 'URL mappings previewed: '.(int) ($plan['url_mappings_preview_count'] ?? 0);

            $mappingReportPath = $this->writeUrlMappingReport($token, $pageMappings, $postMappings);
            if ($mappingReportPath !== null) {
                $parts[] = "URL mapping report: {$mappingReportPath}";
            }
        }

        return [
            'message' => 'Import completed. ' . implode(' · ', $parts),
        ];
    }

    /**
     * @return array{0:int,1:int,2:array<int,array<string,string|null>>} createdPages, skippedPages, mappings
     */
    private function importPages(array $payload, string $mode, int $actorUserId = 0, ?callable $progress = null): array
    {
        if ($mode !== 'create_only') {
            $mode = 'create_only';
        }

        if (!class_exists(TpPage::class)) {
            return [0, 0, []];
        }

        if (!Schema::hasTable('tp_pages')) {
            return [0, 0, []];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0, []];
        }

        $hasStatus = Schema::hasColumn('tp_pages', 'status');
        $hasLayout = Schema::hasColumn('tp_pages', 'layout');
        $hasBlocks = Schema::hasColumn('tp_pages', 'blocks');
        $hasCreatedBy = Schema::hasColumn('tp_pages', 'created_by');
        $hasUpdatedBy = Schema::hasColumn('tp_pages', 'updated_by');

        $created = 0;
        $skipped = 0;
        $mappings = [];

        $total = count($items);
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($slug === '') {
                $skipped++;
                $this->emitProgress($progress, [
                    'kind' => 'entity',
                    'entity' => 'page',
                    'status' => 'skipped',
                    'title' => $title,
                    'slug' => '',
                    'index' => $index + 1,
                    'total' => $total,
                ]);
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

            if ($actorUserId > 0 && $hasCreatedBy) {
                $data['created_by'] = $actorUserId;
            }

            if ($actorUserId > 0 && $hasUpdatedBy) {
                $data['updated_by'] = $actorUserId;
            }

            $model = TpPage::query()->create($data);
            $mappings[] = [
                'type' => 'page',
                'source_url' => (string) ($item['source_link'] ?? ''),
                'source_post_id' => (string) ($item['source_post_id'] ?? ''),
                'destination_url' => '/'.$slug,
            ];
            unset($model);

            $created++;
            $this->emitProgress($progress, [
                'kind' => 'entity',
                'entity' => 'page',
                'status' => 'imported',
                'title' => $title,
                'slug' => $slug,
                'index' => $index + 1,
                'total' => $total,
            ]);
        }

        return [$created, $skipped, $mappings];
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
     * @return array{0:int,1:int,2:array<int,array<string,string|null>>} createdPosts, skippedPosts, mappings
     */
    private function importPosts(array $payload, int $actorUserId = 0, ?callable $progress = null): array
    {
        if (!class_exists(TpPost::class)) {
            return [0, 0, []];
        }

        if (!Schema::hasTable('tp_posts')) {
            return [0, 0, []];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0, []];
        }

        $hasStatus = Schema::hasColumn('tp_posts', 'status');
        $hasLayout = Schema::hasColumn('tp_posts', 'layout');
        $hasBlocks = Schema::hasColumn('tp_posts', 'blocks');
        $hasPublishedAt = Schema::hasColumn('tp_posts', 'published_at');
        $hasAuthor = Schema::hasColumn('tp_posts', 'author_id');
        $hasUsers = Schema::hasTable('tp_users');
        $hasCreatedBy = Schema::hasColumn('tp_posts', 'created_by');
        $hasUpdatedBy = Schema::hasColumn('tp_posts', 'updated_by');

        $created = 0;
        $skipped = 0;
        $mappings = [];

        $total = count($items);
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $slug = trim((string) ($item['slug'] ?? ''));

            if ($slug === '') {
                $skipped++;
                $this->emitProgress($progress, [
                    'kind' => 'entity',
                    'entity' => 'post',
                    'status' => 'skipped',
                    'title' => $title,
                    'slug' => '',
                    'index' => $index + 1,
                    'total' => $total,
                ]);
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
                } elseif ($actorUserId > 0 && $hasUsers && DB::table('tp_users')->where('id', $actorUserId)->exists()) {
                    $data['author_id'] = $actorUserId;
                } else {
                    $data['author_id'] = null;
                }
            }

            if ($actorUserId > 0 && $hasCreatedBy) {
                $data['created_by'] = $actorUserId;
            }

            if ($actorUserId > 0 && $hasUpdatedBy) {
                $data['updated_by'] = $actorUserId;
            }

            $model = TpPost::query()->create($data);
            $mappings[] = [
                'type' => 'post',
                'source_url' => (string) ($item['source_link'] ?? ''),
                'source_post_id' => (string) ($item['source_post_id'] ?? ''),
                'destination_url' => '/'.$this->blogBase().'/'.$slug,
            ];
            unset($model);

            $created++;
            $this->emitProgress($progress, [
                'kind' => 'entity',
                'entity' => 'post',
                'status' => 'imported',
                'title' => $title,
                'slug' => $slug,
                'index' => $index + 1,
                'total' => $total,
            ]);
        }

        return [$created, $skipped, $mappings];
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
     * @return array{0:int,1:int,2:int} createdMedia, skippedMedia, downloadedMedia
     */
    private function importMedia(array $payload, int $actorUserId = 0, ?callable $progress = null): array
    {
        if (!class_exists(TpMedia::class)) {
            return [0, 0, 0];
        }

        if (!Schema::hasTable('tp_media')) {
            return [0, 0, 0];
        }

        $items = $this->itemsFromPayload($payload);
        if ($items === []) {
            return [0, 0, 0];
        }

        $hasCreatedBy = Schema::hasColumn('tp_media', 'created_by');
        $hasUpdatedBy = Schema::hasColumn('tp_media', 'updated_by');

        $created = 0;
        $skipped = 0;
        $downloaded = 0;

        $total = count($items);
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $path = trim((string) ($item['path'] ?? ''));
            if ($path === '') {
                $skipped++;
                $this->emitProgress($progress, [
                    'kind' => 'entity',
                    'entity' => 'media',
                    'status' => 'skipped',
                    'title' => '',
                    'slug' => '',
                    'path' => '',
                    'copied' => false,
                    'index' => $index + 1,
                    'total' => $total,
                ]);
                continue;
            }

            if (DB::table('tp_media')->where('path', $path)->exists()) {
                $skipped++;
                $this->emitProgress($progress, [
                    'kind' => 'entity',
                    'entity' => 'media',
                    'status' => 'skipped',
                    'title' => (string) ($item['title'] ?? ''),
                    'slug' => '',
                    'path' => $path,
                    'copied' => false,
                    'index' => $index + 1,
                    'total' => $total,
                ]);
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

            if ($actorUserId > 0 && $hasCreatedBy) {
                $data['created_by'] = $actorUserId;
            }

            if ($actorUserId > 0 && $hasUpdatedBy) {
                $data['updated_by'] = $actorUserId;
            }

            $model = TpMedia::query()->create($data);
            unset($model);

            $sourceUrl = trim((string) ($item['source_url'] ?? ''));
            $copied = $sourceUrl !== '' && $this->copyRemoteMediaToDisk($sourceUrl, (string) $data['disk'], (string) $data['path']);
            if ($copied) {
                $downloaded++;
            }

            $created++;
            $this->emitProgress($progress, [
                'kind' => 'entity',
                'entity' => 'media',
                'status' => 'imported',
                'title' => (string) ($item['title'] ?? ''),
                'slug' => '',
                'path' => $path,
                'copied' => $copied,
                'index' => $index + 1,
                'total' => $total,
            ]);
        }

        return [$created, $skipped, $downloaded];
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
