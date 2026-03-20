<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;

final class ContentEntryEditorDriverResolver
{
    /**
     * @return array<int,EditorDriverDefinition>
     */
    public function definitions(): array
    {
        if (! class_exists(EditorDriverRegistry::class) || ! app()->bound(EditorDriverRegistry::class)) {
            return [$this->fallbackDefinition()];
        }

        $definitions = resolve(EditorDriverRegistry::class)->allFor('content-types');

        if ($definitions === []) {
            return [$this->fallbackDefinition()];
        }

        return $definitions;
    }

    /**
     * @return array<int,string>
     */
    public function allowedIds(): array
    {
        $ids = [];

        foreach ($this->definitions() as $definition) {
            $ids[] = $definition->id;
        }

        return $ids;
    }

    public function resolve(?string $requested): string
    {
        $requested = trim((string) $requested);

        if ($requested !== '' && in_array($requested, $this->allowedIds(), true)) {
            return $requested;
        }

        return 'blocks';
    }

    private function fallbackDefinition(): EditorDriverDefinition
    {
        return new EditorDriverDefinition(
            id: 'blocks',
            label: 'Blocks Builder',
            description: 'Structured sections and fields.',
            storage: 'blocks',
            usesBlocksEditor: true,
            sortOrder: 10,
        );
    }
}
