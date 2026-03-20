<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Models\TpContentTypeField;
use TentaPress\Media\Models\TpMedia;
use TentaPress\System\Plugin\PluginRegistry;

final readonly class ContentTypeFormDataFactory
{
    public function __construct(
        private ContentEntryRelationResolver $relations,
    ) {
    }

    /**
     * @return array<string,array<int,array<string,mixed>>>
     */
    public function relationOptions(TpContentType $contentType): array
    {
        $options = [];

        foreach ($contentType->fields as $field) {
            if (! $field instanceof TpContentTypeField || (string) $field->field_type !== 'relation') {
                continue;
            }

            $allowedTypeKeys = collect($field->config['allowed_type_keys'] ?? [])
                ->map(fn (mixed $typeKey): string => trim((string) $typeKey))
                ->filter()
                ->values()
                ->all();

            $allowedSources = collect($field->config['allowed_sources'] ?? ['content-types'])
                ->map(fn (mixed $source): string => trim((string) $source))
                ->filter()
                ->values()
                ->all();

            if ($allowedSources === []) {
                $allowedSources = ['content-types'];
            }

            $options[$field->key] = array_map(
                static fn ($reference): array => [
                    'id' => $reference->value(),
                    'title' => $reference->title,
                    'type_label' => $reference->typeLabel,
                    'source' => $reference->source,
                ],
                $this->relations->options($allowedSources, [
                    'content-types' => [
                        'allowed_type_keys' => $allowedTypeKeys,
                    ],
                ])
            );
        }

        return $options;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function blockDefinitions(): array
    {
        if (! class_exists(BlockRegistry::class) || ! app()->bound(BlockRegistry::class)) {
            return [];
        }

        $registry = resolve(BlockRegistry::class);

        if (! is_object($registry) || ! method_exists($registry, 'all')) {
            return [];
        }

        $enabledPluginIds = $this->enabledPluginIds();
        $definitions = [];

        foreach ($registry->all() as $definition) {
            $definitions[] = [
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
            ];
        }

        return array_values(array_filter($definitions, static function (array $definition) use ($enabledPluginIds): bool {
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

            if (! in_array($pluginId, $enabledPluginIds, true) && count($segments) === 2) {
                $pluginId = 'tentapress/'.$segments[0];
            }

            return in_array($pluginId, $enabledPluginIds, true);
        }));
    }

    /**
     * @return array<int,array{id:int,value:string,label:string,original_name:string,mime_type:string,is_image:bool}>
     */
    public function mediaOptions(): array
    {
        if (! class_exists(TpMedia::class) || ! Schema::hasTable('tp_media')) {
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
            $originalName = trim((string) ($item->original_name ?? ''));
            $label = $title !== '' ? $title : ($originalName !== '' ? $originalName : 'Media #'.$item->id);
            $mimeType = (string) ($item->mime_type ?? '');

            $options[] = [
                'id' => (int) $item->id,
                'value' => $url,
                'label' => $label,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'is_image' => $mimeType !== '' && str_starts_with($mimeType, 'image/'),
            ];
        }

        return $options;
    }

    /**
     * @return array<int,string>|null
     */
    private function enabledPluginIds(): ?array
    {
        if (! class_exists(PluginRegistry::class) || ! app()->bound(PluginRegistry::class)) {
            return null;
        }

        $registry = resolve(PluginRegistry::class);

        if (! is_object($registry) || ! method_exists($registry, 'readCache')) {
            return null;
        }

        $cache = $registry->readCache();

        if (! is_array($cache) || $cache === []) {
            return null;
        }

        $ids = array_values(array_filter(array_map(static fn (mixed $id): string => trim((string) $id), array_keys($cache))));

        return $ids === [] ? null : $ids;
    }
}
