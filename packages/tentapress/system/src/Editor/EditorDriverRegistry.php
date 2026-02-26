<?php

declare(strict_types=1);

namespace TentaPress\System\Editor;

final class EditorDriverRegistry
{
    /**
     * @var array<string,EditorDriverDefinition>
     */
    private array $definitions = [];

    public function register(EditorDriverDefinition $definition): void
    {
        $id = trim($definition->id);

        if ($id === '') {
            return;
        }

        $this->definitions[$id] = $definition;
    }

    /**
     * @return array<int,EditorDriverDefinition>
     */
    public function allFor(string $resource): array
    {
        $this->registerLegacyPageDriver($resource);

        $definitions = array_values(array_filter(
            $this->definitions,
            static fn (EditorDriverDefinition $definition): bool => $definition->availableFor($resource),
        ));

        usort($definitions, static function (EditorDriverDefinition $left, EditorDriverDefinition $right): int {
            if ($left->sortOrder !== $right->sortOrder) {
                return $left->sortOrder <=> $right->sortOrder;
            }

            return strcasecmp($left->label, $right->label);
        });

        return $definitions;
    }

    /**
     * @return array<int,string>
     */
    public function idsFor(string $resource): array
    {
        return array_map(static fn (EditorDriverDefinition $definition): string => $definition->id, $this->allFor($resource));
    }

    public function get(string $id, string $resource): ?EditorDriverDefinition
    {
        $id = trim($id);

        if ($id === '') {
            return null;
        }

        $definition = $this->definitions[$id] ?? null;

        if (! $definition instanceof EditorDriverDefinition) {
            return null;
        }

        return $definition->availableFor($resource) ? $definition : null;
    }

    public function resolve(string $requested, string $resource, string $fallback = 'blocks'): string
    {
        $requested = trim($requested);

        if ($requested !== '' && $this->get($requested, $resource) !== null) {
            return $requested;
        }

        if ($this->get($fallback, $resource) !== null) {
            return $fallback;
        }

        $ids = $this->idsFor($resource);

        return $ids[0] ?? $fallback;
    }

    private function registerLegacyPageDriver(string $resource): void
    {
        if (isset($this->definitions['page'])) {
            return;
        }

        $binding = match ($resource) {
            'pages' => 'tp.pages.editor.view',
            'posts' => 'tp.posts.editor.view',
            default => null,
        };

        if (! is_string($binding) || ! app()->bound($binding)) {
            return;
        }

        $view = resolve($binding);

        if (! is_string($view) || $view === '' || ! view()->exists($view)) {
            return;
        }

        $this->register(new EditorDriverDefinition(
            id: 'page',
            label: 'Page Editor',
            description: 'Continuous writing surface.',
            storage: 'content',
            pagesView: $resource === 'pages' ? $view : null,
            postsView: $resource === 'posts' ? $view : null,
            usesBlocksEditor: false,
            sortOrder: 20,
        ));
    }
}
