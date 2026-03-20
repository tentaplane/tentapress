<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Http\Response;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\System\Theme\ThemeManager;

final readonly class ContentEntryRenderer
{
    public function __construct(
        private ContentEntryFieldPresenter $fieldPresenter,
    ) {
    }

    public function render(TpContentEntry $entry): Response
    {
        $entry->loadMissing('contentType.fields');

        $layoutKey = (string) ($entry->layout ?: ($entry->contentType?->default_layout ?: 'default'));
        $blocks = $this->normalizeBlocks($entry->blocks);
        $content = is_array($entry->content) ? $entry->content : null;
        $editorDriver = (string) ($entry->editor_driver ?? '');
        $blocksHtml = $this->renderBody($blocks, $content, $editorDriver);
        $view = $this->resolveLayoutView($layoutKey);
        $presentedFields = $this->fieldPresenter->present(
            $entry->contentType,
            is_array($entry->field_values) ? $entry->field_values : []
        );
        $contentHtml = $this->renderContentHtml($blocksHtml, $presentedFields);
        $usesThemeLayout = $view !== 'tentapress-content-types::layouts.entry';

        return response()->view($view, [
            'entry' => $entry,
            'page' => $entry,
            'contentType' => $entry->contentType,
            'layoutKey' => $layoutKey,
            'blocksHtml' => $usesThemeLayout ? $contentHtml : $blocksHtml,
            'contentHtml' => $contentHtml,
            'isPageEditorContent' => $editorDriver === 'page' && is_array($content),
            'presentedFields' => $presentedFields,
        ]);
    }

    /**
     * @param  array<int,mixed>  $blocks
     * @param  array<string,mixed>|null  $content
     */
    private function renderBody(array $blocks, ?array $content, string $editorDriver): string
    {
        if (($editorDriver === 'page' || $editorDriver === 'builder') && is_array($content) && app()->bound('tp.page_editor.render')) {
            $renderer = resolve('tp.page_editor.render');

            if (is_callable($renderer)) {
                $html = $renderer($content);

                if (is_string($html) && trim($html) !== '') {
                    return $html;
                }
            }
        }

        if (app()->bound('tp.blocks.render')) {
            $renderer = resolve('tp.blocks.render');

            if (is_callable($renderer)) {
                $html = $renderer($blocks);

                if (is_string($html)) {
                    return $html;
                }
            }
        }

        return (string) view('tentapress-content-types::blocks.fallback-list', [
            'blocks' => $blocks,
        ]);
    }

    private function resolveLayoutView(string $layoutKey): string
    {
        if (class_exists(ThemeManager::class) && app()->bound(ThemeManager::class)) {
            $themeView = resolve(ThemeManager::class)->layoutView($layoutKey);

            if (is_string($themeView) && $themeView !== '') {
                return $themeView;
            }
        }

        return 'tentapress-content-types::layouts.entry';
    }

    /**
     * @param  array<int,array<string,mixed>>  $presentedFields
     */
    private function renderContentHtml(string $blocksHtml, array $presentedFields): string
    {
        return (string) view('tentapress-content-types::partials.entry-content', [
            'blocksHtml' => $blocksHtml,
            'presentedFields' => $presentedFields,
        ]);
    }

    /**
     * @return array<int,mixed>
     */
    private function normalizeBlocks(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        if (array_key_exists('blocks', $raw) && is_array($raw['blocks'])) {
            return array_values($raw['blocks']);
        }

        return array_values($raw);
    }
}
