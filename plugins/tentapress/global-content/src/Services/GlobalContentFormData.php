<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Media\Models\TpMedia;
use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;

final class GlobalContentFormData
{
    /**
     * @return array<int,EditorDriverDefinition>
     */
    public function editorDrivers(): array
    {
        if (! app()->bound(EditorDriverRegistry::class)) {
            return [
                new EditorDriverDefinition(
                    id: 'blocks',
                    label: 'Blocks Builder',
                    description: 'Structured sections and fields.',
                    storage: 'blocks',
                    usesBlocksEditor: true,
                    sortOrder: 10,
                ),
            ];
        }

        $registry = app()->make(EditorDriverRegistry::class);
        $allowed = array_filter(
            $registry->allFor('pages'),
            static fn (EditorDriverDefinition $definition): bool => in_array($definition->id, ['blocks', 'builder'], true),
        );

        return $allowed !== [] ? array_values($allowed) : [
            new EditorDriverDefinition(
                id: 'blocks',
                label: 'Blocks Builder',
                description: 'Structured sections and fields.',
                storage: 'blocks',
                usesBlocksEditor: true,
                sortOrder: 10,
            ),
        ];
    }

    /**
     * @return array<int,string>
     */
    public function editorDriverIds(): array
    {
        return array_map(static fn (EditorDriverDefinition $definition): string => $definition->id, $this->editorDrivers());
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function blockDefinitions(): array
    {
        if (! app()->bound(BlockRegistry::class)) {
            return [];
        }

        $registry = app()->make(BlockRegistry::class);
        if (! $registry instanceof BlockRegistry) {
            return [];
        }

        return array_values(array_map(static fn ($definition): array => [
            'type' => (string) ($definition->type ?? ''),
            'name' => (string) ($definition->name ?? ''),
            'description' => (string) ($definition->description ?? ''),
            'version' => (int) ($definition->version ?? 1),
            'fields' => is_array($definition->fields ?? null) ? $definition->fields : [],
            'variants' => is_array($definition->variants ?? null) ? $definition->variants : [],
            'default_variant' => isset($definition->defaultVariant) ? (string) $definition->defaultVariant : null,
            'defaults' => is_array($definition->defaults ?? null) ? $definition->defaults : [],
            'example' => is_array($definition->example ?? null) ? $definition->example : [],
            'view' => isset($definition->view) ? (string) $definition->view : null,
        ], $registry->all()));
    }

    /**
     * @return array<int,array{id:int,value:string,label:string,original_name:string,mime_type:string,is_image:bool}>
     */
    public function mediaOptions(): array
    {
        if (! class_exists(TpMedia::class) || ! Schema::hasTable('tp_media')) {
            return [];
        }

        return TpMedia::query()
            ->latest('created_at')
            ->limit(200)
            ->get(['id', 'title', 'original_name', 'path', 'mime_type', 'disk'])
            ->map(static function (TpMedia $item): ?array {
                $disk = (string) ($item->disk ?? 'public');
                $path = trim((string) ($item->path ?? ''));

                if ($disk !== 'public' || $path === '') {
                    return null;
                }

                $url = '/storage/'.ltrim($path, '/');
                $title = trim((string) ($item->title ?? ''));
                $original = trim((string) ($item->original_name ?? ''));
                $label = $title !== '' ? $title : ($original !== '' ? $original : 'Media #'.$item->id);
                $mime = (string) ($item->mime_type ?? '');

                return [
                    'id' => (int) $item->id,
                    'value' => $url,
                    'label' => $label,
                    'original_name' => $original,
                    'mime_type' => $mime,
                    'is_image' => $mime !== '' && str_starts_with($mime, 'image/'),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
