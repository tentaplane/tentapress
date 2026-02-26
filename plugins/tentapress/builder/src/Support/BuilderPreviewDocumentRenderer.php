<?php

declare(strict_types=1);

namespace TentaPress\Builder\Support;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Log;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Theme\ThemeManager;

final readonly class BuilderPreviewDocumentRenderer
{
    public function __construct(
        private BuilderPreviewBlockRenderer $blocks,
        private BuilderPreviewDocumentExtractor $extractor,
        private ThemeManager $themes,
        private ViewFactory $views,
    ) {
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{
     *     token:string,
     *     revision:string,
     *     lang:string,
     *     body_class:string,
     *     styles:array<int,array{href:string,media:string}>,
     *     inline_styles:array<int,string>,
     *     body_html:string,
     *     block_map:array<int,array{index:int,key:string}>
     * }
     */
    public function render(string $token, array $payload): array
    {
        $resource = trim((string) ($payload['resource'] ?? 'pages')) === 'posts' ? 'posts' : 'pages';
        $layoutKey = trim((string) ($payload['layout'] ?? 'default'));
        $layoutKey = $layoutKey !== '' ? $layoutKey : 'default';

        $renderedBlocks = $this->blocks->render(is_array($payload['blocks'] ?? null) ? $payload['blocks'] : []);

        $resolved = $this->resolveView($resource, $layoutKey);
        $viewName = $resolved['view'];
        $usesFallback = $resolved['uses_fallback'];

        if ($usesFallback) {
            $activeTheme = $this->themes->activeTheme();
            Log::info('builder.preview.fallback_theme_layout', [
                'theme' => is_array($activeTheme) ? (string) ($activeTheme['id'] ?? '') : '',
                'resource' => $resource,
                'layout' => $layoutKey,
                'view' => $viewName,
            ]);
        }

        $html = $resource === 'posts'
            ? $this->renderPostView($viewName, $layoutKey, $payload, $renderedBlocks['html'])
            : $this->renderPageView($viewName, $layoutKey, $payload, $renderedBlocks['html']);

        $document = $this->extractor->extract($html);
        $revision = $this->hashDocument($document, $renderedBlocks['block_map']);

        return [
            'token' => $token,
            'revision' => $revision,
            'lang' => $document['lang'],
            'body_class' => $document['body_class'],
            'styles' => $document['styles'],
            'inline_styles' => $document['inline_styles'],
            'body_html' => $document['body_html'],
            'block_map' => $renderedBlocks['block_map'],
        ];
    }

    /**
     * @param  array<string,mixed>  $payload
     */
    private function renderPageView(string $viewName, string $layoutKey, array $payload, string $blocksHtml): string
    {
        $page = new TpPage([
            'title' => (string) ($payload['title'] ?? 'Preview'),
            'slug' => (string) ($payload['slug'] ?? ''),
            'status' => 'draft',
            'layout' => $layoutKey,
            'editor_driver' => 'builder',
            'blocks' => is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [],
            'content' => null,
        ]);

        return $this->views->make($viewName, [
            'page' => $page,
            'layoutKey' => $layoutKey,
            'blocksHtml' => $blocksHtml,
            'isPageEditorContent' => false,
        ])->render();
    }

    /**
     * @param  array<string,mixed>  $payload
     */
    private function renderPostView(string $viewName, string $layoutKey, array $payload, string $blocksHtml): string
    {
        $post = new TpPost([
            'title' => (string) ($payload['title'] ?? 'Preview'),
            'slug' => (string) ($payload['slug'] ?? ''),
            'status' => 'draft',
            'layout' => $layoutKey,
            'editor_driver' => 'builder',
            'blocks' => is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [],
            'content' => null,
            'author_id' => null,
        ]);

        return $this->views->make($viewName, [
            'post' => $post,
            'page' => $post,
            'layoutKey' => $layoutKey,
            'blocksHtml' => $blocksHtml,
            'isPageEditorContent' => false,
        ])->render();
    }

    /**
     * @return array{view:string,uses_fallback:bool}
     */
    private function resolveView(string $resource, string $layoutKey): array
    {
        $themeView = $this->themes->layoutView($layoutKey);
        if (is_string($themeView) && $themeView !== '') {
            return [
                'view' => $themeView,
                'uses_fallback' => false,
            ];
        }

        return [
            'view' => $resource === 'posts' ? 'tentapress-posts::layouts.post' : 'tentapress-pages::layouts.page',
            'uses_fallback' => true,
        ];
    }

    /**
     * @param  array{
     *     lang:string,
     *     body_class:string,
     *     styles:array<int,array{href:string,media:string}>,
     *     inline_styles:array<int,string>,
     *     body_html:string
     * }  $document
     * @param  array<int,array{index:int,key:string}>  $blockMap
     */
    private function hashDocument(array $document, array $blockMap): string
    {
        $payload = [
            'lang' => $document['lang'],
            'body_class' => $document['body_class'],
            'styles' => $document['styles'],
            'inline_styles' => $document['inline_styles'],
            'body_html' => $document['body_html'],
            'block_map' => $blockMap,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return sha1(is_string($json) ? $json : serialize($payload));
    }
}
