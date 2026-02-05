<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Pages\Models\TpPage;
use TentaPress\System\Theme\ThemeManager;

final class EditController
{
    public function __invoke(TpPage $page, ThemeManager $themes)
    {
        $blocks = $this->normalizeBlocks($page->blocks);
        $blocksJson = json_encode($blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($blocksJson === false) {
            $blocksJson = '[]';
        }

        $pageDoc = is_array($page->content) ? $page->content : ['time' => 0, 'blocks' => [], 'version' => '2.28.0'];
        $pageDocJson = json_encode($pageDoc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($pageDocJson === false) {
            $pageDocJson = '{"time":0,"blocks":[],"version":"2.28.0"}';
        }

        return view('tentapress-pages::pages.form', [
            'mode' => 'edit',
            'page' => $page,
            'blocksJson' => $blocksJson,
            'pageDocJson' => $pageDocJson,
            'themeLayouts' => $themes->activeLayouts(),
            'hasTheme' => $themes->hasActiveTheme(),
            'blockDefinitions' => $this->blockDefinitions(),
            'mediaOptions' => $this->mediaOptions(),
        ]);
    }

    /**
     * @return array<int,array{type:string,name:string,description:string,example:array}>
     */
    private function blockDefinitions(): array
    {
        $registryClass = BlockRegistry::class;

        if (! class_exists($registryClass)) {
            return [];
        }

        if (! app()->bound($registryClass)) {
            return [];
        }

        $registry = resolve($registryClass);

        if (! is_object($registry) || ! method_exists($registry, 'all')) {
            return [];
        }

        $defs = $registry->all();

        $out = [];

        foreach ($defs as $def) {
            $out[] = [
                'type' => (string) ($def->type ?? ''),
                'name' => (string) ($def->name ?? ''),
                'description' => (string) ($def->description ?? ''),
                'version' => (int) ($def->version ?? 1),
                'fields' => is_array($def->fields ?? null) ? $def->fields : [],
                'variants' => is_array($def->variants ?? null) ? $def->variants : [],
                'default_variant' => isset($def->defaultVariant) ? (string) $def->defaultVariant : null,
                'defaults' => is_array($def->defaults ?? null) ? $def->defaults : [],
                'example' => is_array($def->example ?? null) ? $def->example : [],
                'view' => isset($def->view) ? (string) $def->view : null,
            ];
        }

        $enabledPluginIds = $this->enabledPluginIds();

        return array_values(array_filter($out, static function (array $definition) use ($enabledPluginIds): bool {
            $type = trim((string) ($definition['type'] ?? ''));
            if ($type === '') {
                return false;
            }

            if ($enabledPluginIds === null || str_starts_with($type, 'blocks/')) {
                return true;
            }

            $segments = explode('/', $type, 3);
            if (count($segments) < 2) {
                return true;
            }

            $pluginId = $segments[0].'/'.$segments[1];

            return in_array($pluginId, $enabledPluginIds, true);
        }));
    }

    /**
     * @return array<int,string>|null
     */
    private function enabledPluginIds(): ?array
    {
        $registryClass = PluginRegistry::class;

        if (! class_exists($registryClass) || ! app()->bound($registryClass)) {
            return null;
        }

        $registry = resolve($registryClass);
        if (! is_object($registry) || ! method_exists($registry, 'readCache')) {
            return null;
        }

        $cache = $registry->readCache();
        if (! is_array($cache) || $cache === []) {
            return null;
        }

        $ids = array_values(array_filter(array_map(static fn ($id): string => trim((string) $id), array_keys($cache))));

        return $ids === [] ? null : $ids;
    }

    /**
     * @return array<int,array{value:string,label:string,original_name:string,mime_type:string,is_image:bool}>
     */
    private function mediaOptions(): array
    {
        if (! class_exists(TpMedia::class)) {
            return [];
        }

        if (! Schema::hasTable('tp_media')) {
            return [];
        }

        $items = TpMedia::query()
            ->latest('created_at')
            ->limit(200)
            ->get(['id', 'title', 'original_name', 'path', 'mime_type', 'disk']);

        $options = [];

        foreach ($items as $item) {
            $disk = (string) ($item->disk ?? 'public');
            $path = trim((string) ($item->path ?? ''));
            if ($disk !== 'public' || $path === '') {
                continue;
            }

            $url = '/storage/'.ltrim($path, '/');
            $title = trim((string) ($item->title ?? ''));
            $original = trim((string) ($item->original_name ?? ''));
            $label = $title !== '' ? $title : ($original !== '' ? $original : 'Media #'.$item->id);

            $mime = (string) ($item->mime_type ?? '');
            $isImage = $mime !== '' && str_starts_with($mime, 'image/');

            $options[] = [
                'value' => $url,
                'label' => $label,
                'original_name' => $original,
                'mime_type' => $mime,
                'is_image' => $isImage,
            ];
        }

        return $options;
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
