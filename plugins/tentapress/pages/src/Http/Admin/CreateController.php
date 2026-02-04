<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Pages\Models\TpPage;
use TentaPress\System\Theme\ThemeManager;

final class CreateController
{
    public function __invoke(ThemeManager $themes)
    {
        $page = new TpPage([
            'title' => '',
            'slug' => '',
            'status' => 'draft',
            'layout' => null,
            'blocks' => [],
            'content' => ['type' => 'page', 'content' => []],
        ]);

        $pageDocJson = json_encode($page->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($pageDocJson === false) {
            $pageDocJson = '{"type":"page","content":[]}';
        }

        return view('tentapress-pages::pages.form', [
            'mode' => 'create',
            'page' => $page,
            'blocksJson' => '[]',
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

        return array_values(array_filter($out, static fn ($d) => ($d['type'] ?? '') !== ''));
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
}
