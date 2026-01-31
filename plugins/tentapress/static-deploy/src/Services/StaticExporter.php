<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Services;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\System\Theme\ThemeManager;
use ZipArchive;

final class StaticExporter
{
    private const MAX_REDIRECTS = 5;

    /**
     * @param array{
     *   include_favicon?:bool,
     *   include_robots?:bool,
     *   compress_html?:bool
     * } $options
     *
     * @return array{
     *   timestamp:string,
     *   build_dir:string,
     *   zip_path:string,
     *   pages_total:int,
     *   pages_written:int,
     *   warnings:array<int,string>
     * }
     */
    public function build(array $options = []): array
    {
        $includeFavicon = (bool) ($options['include_favicon'] ?? true);
        $includeRobots = (bool) ($options['include_robots'] ?? true);
        $compressHtml = (bool) ($options['compress_html'] ?? false);

        $productionState = $this->enableProductionOutput();

        try {
            $timestamp = gmdate('Ymd-His');

            $base = storage_path('app/tp-static');
            $buildDir = $base . DIRECTORY_SEPARATOR . 'builds' . DIRECTORY_SEPARATOR . $timestamp;
            $exportsDir = $base . DIRECTORY_SEPARATOR . 'exports';

            File::ensureDirectoryExists($buildDir);
            File::ensureDirectoryExists($exportsDir);

            $warnings = [];

            $paths = $this->pathsToExport($warnings);

            $pagesTotal = count($paths);
            $pagesWritten = 0;

            foreach ($paths as $path) {
                $result = $this->renderPathFollowingRedirects($path);

                if ($result['ok'] !== true) {
                    $warnings[] = (string) $result['warning'];
                    continue;
                }

                $html = $this->rewriteAssetUrls((string) $result['html']);
                $html = $this->stripBoostScripts($html);

                if ($compressHtml) {
                    $html = $this->compressHtml($html);
                }

                $target = $this->targetFileForPath($buildDir, $path);

                File::ensureDirectoryExists(dirname($target));
                File::put($target, $html);

                $pagesWritten++;
            }

            $publicTarget = $buildDir . DIRECTORY_SEPARATOR . 'public';
            File::ensureDirectoryExists($publicTarget);

            $excludeAdmin = [
                'admin.js',
                'admin.css',
                '*admin*.js',
                '*admin*.css',
                'theme*.css',
            ];

            $this->copyActiveThemeAssetsFlat($publicTarget, $warnings);
            $this->copyPublicStorage($publicTarget . DIRECTORY_SEPARATOR . 'storage', $warnings);

            $this->copyDirFiltered(public_path('assets'), $publicTarget . DIRECTORY_SEPARATOR . 'assets', $warnings, $excludeAdmin);

            if ($includeFavicon) {
                $this->copyFileIfExists(public_path('favicon.ico'), $publicTarget . DIRECTORY_SEPARATOR . 'favicon.ico', $warnings);
            }

            if ($includeRobots) {
                $this->copyFileIfExists(public_path('robots.txt'), $publicTarget . DIRECTORY_SEPARATOR . 'robots.txt', $warnings);
            }

            $zipPath = $exportsDir . DIRECTORY_SEPARATOR . 'static-' . $timestamp . '.zip';
            $this->zipDirectory($buildDir, $zipPath);

            $last = [
                'timestamp' => $timestamp,
                'build_dir' => $buildDir,
                'zip_path' => $zipPath,
                'pages_total' => $pagesTotal,
                'pages_written' => $pagesWritten,
                'warnings' => $warnings,
                'generated_at_utc' => gmdate('c'),
            ];

            File::put($base . DIRECTORY_SEPARATOR . 'last.json', $this->json($last));

            return [
                'timestamp' => $timestamp,
                'build_dir' => $buildDir,
                'zip_path' => $zipPath,
                'pages_total' => $pagesTotal,
                'pages_written' => $pagesWritten,
                'warnings' => $warnings,
            ];
        } finally {
            $this->disableProductionOutput($productionState);
        }
    }

    /**
     * @return array<string,mixed>|null
     */
    public function lastBuildInfo(): ?array
    {
        $path = storage_path('app/tp-static/last.json');

        if (!is_file($path)) {
            return null;
        }

        $raw = File::get($path);

        try {
            $decoded = json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function lastZipPath(): ?string
    {
        $last = $this->lastBuildInfo();

        if (!is_array($last)) {
            return null;
        }

        $zip = $last['zip_path'] ?? null;

        return is_string($zip) && $zip !== '' ? $zip : null;
    }

    /**
     * @param array<int,string> $warnings
     * @return array<int,string>
     */
    private function pathsToExport(array &$warnings): array
    {
        $paths = ['/'];
        $blogBase = $this->resolveBlogBase();

        if (!class_exists(TpPage::class)) {
            $warnings[] = 'Pages plugin not found: exporting home only.';
        } elseif (!Schema::hasTable('tp_pages')) {
            $warnings[] = 'tp_pages table not found: exporting home only.';
        } else {
            $query = TpPage::query()->orderBy('id');

            if (Schema::hasColumn('tp_pages', 'status')) {
                $query->where('status', 'published');
            }

            $pages = $query->get(['slug'])->all();

            foreach ($pages as $p) {
                $slug = trim((string) ($p->slug ?? ''));

                if ($slug === '') {
                    continue;
                }

                // v0: skip nested slugs to keep output predictable
                if (Str::contains($slug, '/')) {
                    $warnings[] = 'Skipped slug containing "/": ' . $slug;
                    continue;
                }

                if ($slug === $blogBase) {
                    $warnings[] = 'Skipped page slug reserved for blog index: ' . $slug;
                    continue;
                }

                if (Str::startsWith($slug, 'admin')) {
                    $warnings[] = 'Skipped reserved slug: ' . $slug;
                    continue;
                }

                if (in_array($slug, ['api', 'storage', 'vendor'], true)) {
                    $warnings[] = 'Skipped reserved slug: ' . $slug;
                    continue;
                }

                $paths[] = '/' . ltrim($slug, '/');
            }
        }

        $paths = $this->appendPostsPaths($paths, $blogBase, $warnings);

        return array_values(array_unique($paths));
    }

    /**
     * @param array<int,string> $paths
     * @param array<int,string> $warnings
     * @return array<int,string>
     */
    private function appendPostsPaths(array $paths, string $blogBase, array &$warnings): array
    {
        if (!class_exists(TpPost::class)) {
            $warnings[] = 'Posts plugin not found: skipping blog routes.';

            return $paths;
        }

        if (!Schema::hasTable('tp_posts')) {
            $warnings[] = 'tp_posts table not found: skipping blog routes.';

            return $paths;
        }

        $paths[] = '/' . ltrim($blogBase, '/');

        $query = TpPost::query()->orderBy('id');

        if (Schema::hasColumn('tp_posts', 'status')) {
            $query->where('status', 'published');
        }

        if (Schema::hasColumn('tp_posts', 'published_at')) {
            $query->where(function ($builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        $posts = $query->get(['slug'])->all();

        foreach ($posts as $post) {
            $slug = trim((string) ($post->slug ?? ''));

            if ($slug === '') {
                continue;
            }

            if (Str::contains($slug, '/')) {
                $warnings[] = 'Skipped post slug containing "/": ' . $slug;
                continue;
            }

            $paths[] = '/' . trim($blogBase, '/') . '/' . ltrim($slug, '/');
        }

        return $paths;
    }

    private function resolveBlogBase(): string
    {
        $blogBase = 'blog';

        if (class_exists(SettingsStore::class) && app()->bound(SettingsStore::class)) {
            $rawBase = (string) resolve(SettingsStore::class)->get('site.blog_base', '');
            $rawBase = trim($rawBase, '/');

            if ($rawBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1) {
                $blogBase = $rawBase;
            }
        }

        return $blogBase;
    }

    /**
     * Follow internal redirects and return the final HTML.
     *
     * @return array{ok:bool,html?:string,warning?:string}
     */
    private function renderPathFollowingRedirects(string $path): array
    {
        $path = '/' . ltrim($path, '/');

        $appUrl = (string) config('app.url', 'http://localhost');
        $appUrl = rtrim($appUrl, '/');

        $server = $this->serverParamsFromAppUrl($appUrl);

        $kernel = resolve(Kernel::class);

        $originalRequest = null;

        $currentPath = $path;

        try {
            if (app()->bound('request')) {
                $originalRequest = resolve('request');
            }

            for ($i = 0; $i <= self::MAX_REDIRECTS; $i++) {
                $fullUrl = $appUrl . $currentPath;

                $symfony = SymfonyRequest::create($fullUrl, 'GET', [], [], [], $server);
                $symfony->headers->set('Accept', 'text/html');

                $request = IlluminateRequest::createFromBase($symfony);

                // Ensure Illuminate request is in container for helpers/middleware
                app()->instance('request', $request);

                $response = $kernel->handle($request);
                $kernel->terminate($request, $response);

                $status = (int) $response->getStatusCode();

                if ($status >= 300 && $status < 400) {
                    $location = (string) ($response->headers->get('Location') ?? '');

                    if ($location === '') {
                        return [
                            'ok' => false,
                            'warning' => 'Skipped ' . $path . ' (redirect with no Location).',
                        ];
                    }

                    $next = $this->redirectLocationToPath($location, $appUrl);

                    if ($next === null) {
                        return [
                            'ok' => false,
                            'warning' => 'Skipped ' . $path . ' (redirect outside site to ' . $location . ').',
                        ];
                    }

                    $currentPath = $next;
                    continue;
                }

                if ($status !== 200) {
                    return [
                        'ok' => false,
                        'warning' => 'Skipped ' . $path . ' (status ' . $status . ').',
                    ];
                }

                $contentType = (string) ($response->headers->get('Content-Type') ?? '');

                if ($contentType !== '' && !Str::contains(strtolower($contentType), 'text/html')) {
                    return [
                        'ok' => false,
                        'warning' => 'Skipped ' . $path . ' (non-HTML Content-Type: ' . $contentType . ').',
                    ];
                }

                $html = (string) $response->getContent();

                if (trim($html) === '') {
                    return [
                        'ok' => false,
                        'warning' => 'Skipped ' . $path . ' (empty response).',
                    ];
                }

                return [
                    'ok' => true,
                    'html' => $html,
                ];
            }

            return [
                'ok' => false,
                'warning' => 'Skipped ' . $path . ' (too many redirects).',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'warning' => 'Render failed for ' . $path . ': ' . $e->getMessage(),
            ];
        } finally {
            if ($originalRequest !== null) {
                app()->instance('request', $originalRequest);
            }
        }
    }

    private function redirectLocationToPath(string $location, string $appUrl): ?string
    {
        $location = trim($location);

        if (Str::startsWith($location, '/')) {
            return $location;
        }

        if (!Str::contains($location, '://')) {
            return '/' . ltrim($location, '/');
        }

        $from = parse_url($appUrl);
        $to = parse_url($location);

        if (!is_array($from) || !is_array($to)) {
            return null;
        }

        $fromHost = (string) ($from['host'] ?? '');
        $toHost = (string) ($to['host'] ?? '');

        if ($fromHost === '' || $toHost === '' || strtolower($fromHost) !== strtolower($toHost)) {
            return null;
        }

        $path = (string) ($to['path'] ?? '/');
        $query = isset($to['query']) ? ('?' . (string) $to['query']) : '';

        return ($path === '' ? '/' : $path) . $query;
    }

    private function targetFileForPath(string $buildDir, string $path): string
    {
        $path = '/' . ltrim($path, '/');

        if ($path === '/') {
            return $buildDir . DIRECTORY_SEPARATOR . 'index.html';
        }

        $slug = ltrim($path, '/');

        return $buildDir . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'index.html';
    }

    private function rewriteAssetUrls(string $html): string
    {
        $appUrl = (string) config('app.url', 'http://localhost');
        $appUrl = rtrim($appUrl, '/');

        $html = $this->rewriteViteDevUrls($html);
        $html = $this->rewriteThemeBuildUrls($html);
        $html = $this->rewriteAbsoluteAppUrls($html, $appUrl);

        // Absolute URLs first
        $html = $this->rewriteAttrPrefixes($html, $appUrl . '/assets/', '/public/assets/');
        $html = $this->rewriteAttrPrefixes($html, $appUrl . '/themes/', '/public/themes/');
        $html = $this->rewriteAttrPrefixes($html, $appUrl . '/storage/', '/public/storage/');
        $html = $this->rewriteAttrPrefixes($html, $appUrl . '/favicon.ico', '/public/favicon.ico');
        $html = $this->rewriteAttrPrefixes($html, $appUrl . '/robots.txt', '/public/robots.txt');

        // Root-absolute paths
        $html = $this->rewriteAttrPrefixes($html, '/assets/', '/public/assets/');
        $html = $this->rewriteAttrPrefixes($html, '/themes/', '/public/themes/');
        $html = $this->rewriteAttrPrefixes($html, '/storage/', '/public/storage/');
        $html = $this->rewriteAttrPrefixes($html, '/favicon.ico', '/public/favicon.ico');
        $html = $this->rewriteAttrPrefixes($html, '/robots.txt', '/public/robots.txt');

        return $html;
    }

    private function stripBoostScripts(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $html = preg_replace(
            '/<script[^>]*id=["\']browser-logger-active["\'][^>]*>.*?<\\/script>/is',
            '',
            $html
        ) ?? $html;

        $html = preg_replace(
            '/<script[^>]*>.*?browser-logger-active.*?<\\/script>/is',
            '',
            $html
        ) ?? $html;

        return $html;
    }

    private function compressHtml(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        $placeholders = [];
        $index = 0;

        $html = preg_replace_callback('/<(pre|textarea|script|style)(\\b[^>]*)?>.*?<\\/\\1>/is', function (array $m) use (&$placeholders, &$index) {
            $key = '___TP_HTML_BLOCK_' . $index . '___';
            $placeholders[$key] = $m[0];
            $index++;

            return $key;
        }, $html) ?? $html;

        $html = preg_replace_callback('/<!--(.*?)-->/s', function (array $m) {
            $content = $m[1] ?? '';

            if (str_contains($content, '[if') || str_contains($content, '<![endif')) {
                return $m[0];
            }

            return '';
        }, $html) ?? $html;

        $html = preg_replace('/>\\s+</', '><', $html) ?? $html;
        $html = preg_replace('/\\s{2,}/', ' ', $html) ?? $html;
        $html = trim($html);

        if ($placeholders !== []) {
            $html = str_replace(array_keys($placeholders), array_values($placeholders), $html);
        }

        return $html;
    }

    private function rewriteViteDevUrls(string $html): string
    {
        $map = $this->viteManifestMap();

        if ($map === []) {
            return $html;
        }

        $html = preg_replace('/<script[^>]+src=["\'][^"\']*\\/(@vite|@react-refresh)[^"\']*["\'][^>]*><\\/script>/i', '', $html) ?? $html;

        $attrs = ['href', 'src', 'content', 'data-src'];

        foreach ($attrs as $attr) {
            $pattern = '/\\b' . preg_quote($attr, '/') . '\\s*=\\s*(["\\\'])(.*?)\\1/i';

            $html = preg_replace_callback($pattern, function (array $m) use ($attr, $map) {
                $q = $m[1];
                $val = $m[2];

                $path = null;

                if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                    $parts = parse_url($val);
                    $path = isset($parts['path']) ? ltrim((string) $parts['path'], '/') : null;
                } elseif (str_starts_with($val, '/')) {
                    $path = ltrim($val, '/');
                }

                if ($path === null || !isset($map[$path])) {
                    return $m[0];
                }

                return $attr . '=' . $q . $map[$path] . $q;
            }, $html) ?? $html;
        }

        return $html;
    }

    /**
     * @return array<string,string>
     */
    private function viteManifestMap(): array
    {
        $map = [];

        $themeMap = $this->activeThemeFlatInputMap();
        if ($themeMap !== []) {
            $map = array_merge($map, $themeMap);
        }

        return $map;
    }

    /**
     * @return array<string,string>
     */
    private function readViteManifest(string $path, string $publicPrefix): array
    {
        if (!is_file($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $decoded = [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $map = [];

        foreach ($decoded as $input => $data) {
            if (!is_array($data)) {
                continue;
            }

            $file = $data['file'] ?? null;
            if (!is_string($file) || $file === '') {
                continue;
            }

            $map[(string) $input] = $publicPrefix . ltrim($file, '/');
        }

        return $map;
    }

    /**
     * @return array{buildDir:string,manifest:array<string,mixed>}|null
     */
    private function activeThemeManifest(): ?array
    {
        $themeBuildDir = $this->resolveThemeBuildDirectory();
        if (!is_string($themeBuildDir)) {
            return null;
        }

        $manifestPath = public_path($themeBuildDir . '/manifest.json');
        if (!is_file($manifestPath)) {
            return null;
        }

        try {
            $decoded = json_decode((string) File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        return [
            'buildDir' => $themeBuildDir,
            'manifest' => $decoded,
        ];
    }

    /**
     * @return array{css?:string,js?:string}
     */
    private function activeThemeEntryFiles(): array
    {
        $data = $this->activeThemeManifest();
        if ($data === null) {
            return [];
        }

        $manifest = $data['manifest'];

        $css = $manifest['resources/css/theme.css']['file'] ?? null;
        $js = $manifest['resources/js/theme.js']['file'] ?? null;

        return [
            'css' => is_string($css) ? $css : null,
            'js' => is_string($js) ? $js : null,
        ];
    }

    /**
     * @return array<string,string>
     */
    private function activeThemeFlatInputMap(): array
    {
        $files = $this->activeThemeEntryFiles();

        $map = [];

        if (!empty($files['css'])) {
            $map['resources/css/theme.css'] = '/public/theme.css';
        }

        if (!empty($files['js'])) {
            $map['resources/js/theme.js'] = '/public/theme.js';
        }

        return $map;
    }

    /**
     * @return array<string,string>
     */
    private function activeThemeFlatOutputMap(): array
    {
        $data = $this->activeThemeManifest();
        if ($data === null) {
            return [];
        }

        $buildDir = trim($data['buildDir'], '/');
        $files = $this->activeThemeEntryFiles();

        $map = [];

        if (!empty($files['css'])) {
            $map[$buildDir . '/' . ltrim((string) $files['css'], '/')] = '/public/theme.css';
        }

        if (!empty($files['js'])) {
            $map[$buildDir . '/' . ltrim((string) $files['js'], '/')] = '/public/theme.js';
        }

        return $map;
    }

    private function rewriteThemeBuildUrls(string $html): string
    {
        $map = $this->activeThemeFlatOutputMap();

        if ($map === []) {
            return $html;
        }

        return $this->rewriteAttrMap($html, $map);
    }

    /**
     * @param array<string,string> $map
     */
    private function rewriteAttrMap(string $html, array $map): string
    {
        $attrs = ['href', 'src', 'content', 'data-src'];

        foreach ($attrs as $attr) {
            $pattern = '/\\b' . preg_quote($attr, '/') . '\\s*=\\s*(["\\\'])(.*?)\\1/i';

            $html = preg_replace_callback($pattern, function (array $m) use ($attr, $map) {
                $q = $m[1];
                $val = $m[2];

                $path = null;

                if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
                    $parts = parse_url($val);
                    $path = isset($parts['path']) ? ltrim((string) $parts['path'], '/') : null;
                } elseif (str_starts_with($val, '/')) {
                    $path = ltrim($val, '/');
                }

                if ($path === null || !isset($map[$path])) {
                    return $m[0];
                }

                return $attr . '=' . $q . $map[$path] . $q;
            }, $html) ?? $html;
        }

        return $html;
    }

    private function resolveThemeBuildDirectory(): ?string
    {
        if (!class_exists(ThemeManager::class) || !app()->bound(ThemeManager::class)) {
            return null;
        }

        $theme = resolve(ThemeManager::class)->activeTheme();

        if (!is_array($theme)) {
            return null;
        }

        $path = trim((string) ($theme['path'] ?? ''), '/');

        if ($path === '') {
            return null;
        }

        return 'themes/' . $path . '/build';
    }

    /**
     * @return array{env:array<string,bool|string>,config:array<string,mixed>}
     */
    private function enableProductionOutput(): array
    {
        $env = [
            'BOOST_ENABLED' => getenv('BOOST_ENABLED'),
            'APP_ENV' => getenv('APP_ENV'),
            'APP_DEBUG' => getenv('APP_DEBUG'),
        ];

        putenv('BOOST_ENABLED=false');
        putenv('APP_ENV=production');
        putenv('APP_DEBUG=false');

        $config = [
            'boost.enabled' => config('boost.enabled'),
            'boost.browser_logs' => config('boost.browser_logs'),
            'boost.browser_logs_watcher' => config('boost.browser_logs_watcher'),
            'app.env' => config('app.env'),
            'app.debug' => config('app.debug'),
        ];

        config([
            'boost.enabled' => false,
            'boost.browser_logs' => false,
            'boost.browser_logs_watcher' => false,
            'app.env' => 'production',
            'app.debug' => false,
        ]);

        return [
            'env' => $env,
            'config' => $config,
        ];
    }

    /**
     * @param array{env:array<string,bool|string>,config:array<string,mixed>} $state
     */
    private function disableProductionOutput(array $state): void
    {
        $env = $state['env'] ?? [];
        $this->restoreEnv('BOOST_ENABLED', $env);
        $this->restoreEnv('APP_ENV', $env);
        $this->restoreEnv('APP_DEBUG', $env);

        $config = $state['config'] ?? [];
        if (is_array($config) && $config !== []) {
            config($config);
        }
    }

    /**
     * @param array<string,bool|string> $env
     */
    private function restoreEnv(string $key, array $env): void
    {
        if (!array_key_exists($key, $env)) {
            return;
        }

        $value = $env[$key];

        if ($value === false) {
            putenv($key);
        } else {
            putenv($key . '=' . $value);
        }
    }

    private function rewriteAbsoluteAppUrls(string $html, string $appUrl): string
    {
        if ($appUrl === '') {
            return $html;
        }

        $attrs = ['href', 'src', 'content', 'data-src'];

        foreach ($attrs as $attr) {
            $pattern = '/\b' . preg_quote($attr, '/') . '\s*=\s*(["\'])(.*?)\1/i';

            $html = preg_replace_callback($pattern, function (array $m) use ($attr, $appUrl) {
                $q = $m[1];
                $val = $m[2];

                if (!str_starts_with($val, $appUrl)) {
                    return $m[0];
                }

                $newVal = substr($val, strlen($appUrl));
                if ($newVal === '') {
                    $newVal = '/';
                } elseif (!str_starts_with($newVal, '/')) {
                    $newVal = '/' . $newVal;
                }

                return $attr . '=' . $q . $newVal . $q;
            }, $html) ?? $html;
        }

        $html = preg_replace_callback('/\bsrcset\s*=\s*(["\'])(.*?)\1/i', function (array $m) use ($appUrl) {
            $q = $m[1];
            $val = $m[2];

            $parts = array_map(trim(...), explode(',', $val));
            $out = [];

            foreach ($parts as $p) {
                if ($p === '') {
                    continue;
                }

                $chunks = preg_split('/\s+/', $p, 2);
                $url = $chunks[0] ?? '';
                $desc = $chunks[1] ?? null;

                if (str_starts_with($url, $appUrl)) {
                    $url = substr($url, strlen($appUrl));
                    $url = $url === '' ? '/' : (str_starts_with($url, '/') ? $url : '/' . $url);
                }

                $out[] = $desc ? ($url . ' ' . $desc) : $url;
            }

            return 'srcset=' . $q . implode(', ', $out) . $q;
        }, $html) ?? $html;

        return $html;
    }

    private function rewriteAttrPrefixes(string $html, string $from, string $to): string
    {
        if ($from === '' || $from === $to) {
            return $html;
        }

        $attrs = ['href', 'src', 'content', 'data-src'];

        foreach ($attrs as $attr) {
            $pattern = '/\b' . preg_quote($attr, '/') . '\s*=\s*(["\'])(.*?)\1/i';

            $html = preg_replace_callback($pattern, function (array $m) use ($attr, $from, $to) {
                $q = $m[1];
                $val = $m[2];

                if (!str_starts_with($val, $from)) {
                    return $m[0];
                }

                $newVal = $to . substr($val, strlen($from));

                return $attr . '=' . $q . $newVal . $q;
            }, $html) ?? $html;
        }

        // srcset needs special handling
        $html = preg_replace_callback('/\bsrcset\s*=\s*(["\'])(.*?)\1/i', function (array $m) use ($from, $to) {
            $q = $m[1];
            $val = $m[2];

            $parts = array_map(trim(...), explode(',', $val));
            $out = [];

            foreach ($parts as $p) {
                if ($p === '') {
                    continue;
                }

                $chunks = preg_split('/\s+/', $p, 2);
                $url = $chunks[0] ?? '';
                $desc = $chunks[1] ?? null;

                if (str_starts_with($url, $from)) {
                    $url = $to . substr($url, strlen($from));
                }

                $out[] = $desc ? ($url . ' ' . $desc) : $url;
            }

            return 'srcset=' . $q . implode(', ', $out) . $q;
        }, $html) ?? $html;

        return $html;
    }

    /**
     * @param array<int,string> $warnings
     * @param array<int,string> $excludePatterns
     */
    private function copyDirFiltered(string $from, string $to, array &$warnings, array $excludePatterns = []): void
    {
        if (!is_dir($from)) {
            return;
        }

        try {
            File::ensureDirectoryExists($to);

            $from = rtrim($from, DIRECTORY_SEPARATOR);
            $to = rtrim($to, DIRECTORY_SEPARATOR);

            foreach (File::allFiles($from) as $file) {
                $full = $file->getPathname();

                $relative = ltrim(str_replace($from, '', $full), DIRECTORY_SEPARATOR);
                $relativeUnix = str_replace('\\', '/', $relative);

                if ($this->matchesAnyPattern($relativeUnix, $excludePatterns)) {
                    continue;
                }

                $dest = $to . DIRECTORY_SEPARATOR . $relative;

                File::ensureDirectoryExists(dirname($dest));
                File::copy($full, $dest);
            }
        } catch (\Throwable $e) {
            $warnings[] = 'Failed to copy directory ' . $from . ': ' . $e->getMessage();
        }
    }

    /**
     * @param array<int,string> $patterns
     */
    private function matchesAnyPattern(string $relativePath, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);

            if ($pattern === '') {
                continue;
            }

            if (fnmatch($pattern, $relativePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $warnings
     */
    private function copyFileIfExists(string $from, string $to, array &$warnings): void
    {
        if (!is_file($from)) {
            return;
        }

        try {
            File::ensureDirectoryExists(dirname($to));
            File::copy($from, $to);
        } catch (\Throwable $e) {
            $warnings[] = 'Failed to copy file ' . $from . ': ' . $e->getMessage();
        }
    }

    /**
     * @param array<int,string> $warnings
     */
    private function copyPublicStorage(string $target, array &$warnings): void
    {
        $storagePublic = storage_path('app/public');
        if (is_dir($storagePublic)) {
            $this->copyDirFiltered($storagePublic, $target, $warnings);

            return;
        }

        $publicStorage = public_path('storage');
        $this->copyDirFiltered($publicStorage, $target, $warnings);
    }

    /**
     * @param array<int,string> $warnings
     */
    private function copyActiveThemeAssetsFlat(string $publicTarget, array &$warnings): void
    {
        $data = $this->activeThemeManifest();
        if ($data === null) {
            $warnings[] = 'No active theme build manifest found; skipping theme assets.';
            return;
        }

        $themeBuildDir = $data['buildDir'];
        $files = $this->activeThemeEntryFiles();

        $fromBase = public_path($themeBuildDir);
        if (!is_dir($fromBase)) {
            $warnings[] = 'Active theme build directory missing: ' . $fromBase;
            return;
        }

        if (!empty($files['css'])) {
            $from = $fromBase . DIRECTORY_SEPARATOR . $files['css'];
            $to = $publicTarget . DIRECTORY_SEPARATOR . 'theme.css';
            $this->copyFileIfExists($from, $to, $warnings);
        }

        if (!empty($files['js'])) {
            $from = $fromBase . DIRECTORY_SEPARATOR . $files['js'];
            $to = $publicTarget . DIRECTORY_SEPARATOR . 'theme.js';
            $this->copyFileIfExists($from, $to, $warnings);
        }
    }

    private function zipDirectory(string $dir, string $zipPath): void
    {
        $zip = new ZipArchive();

        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        throw_if($opened !== true, \RuntimeException::class, 'Unable to create zip file: ' . $zipPath);

        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        foreach (File::allFiles($dir) as $file) {
            $full = $file->getPathname();
            $relative = ltrim(str_replace($dir, '', $full), DIRECTORY_SEPARATOR);
            $relative = str_replace('\\', '/', $relative);

            $zip->addFile($full, $relative);
        }

        $zip->close();
    }

    /**
     * @return array<string,string>
     */
    private function serverParamsFromAppUrl(string $appUrl): array
    {
        $parts = parse_url($appUrl);

        $scheme = isset($parts['scheme']) ? (string) $parts['scheme'] : 'http';
        $host = isset($parts['host']) ? (string) $parts['host'] : 'localhost';
        $port = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);

        return [
            'HTTP_HOST' => $host,
            'SERVER_NAME' => $host,
            'SERVER_PORT' => (string) $port,
            'HTTPS' => $scheme === 'https' ? 'on' : 'off',
            'REQUEST_SCHEME' => $scheme,
        ];
    }

    private function json(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }
}
