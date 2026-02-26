<?php

declare(strict_types=1);

namespace TentaPress\System\Editor;

final readonly class EditorDriverDefinition
{
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public string $storage,
        public ?string $pagesView = null,
        public ?string $postsView = null,
        public bool $usesBlocksEditor = false,
        public int $sortOrder = 100,
    ) {
    }

    public function viewFor(string $resource): ?string
    {
        return match ($resource) {
            'pages' => $this->pagesView,
            'posts' => $this->postsView,
            default => null,
        };
    }

    public function availableFor(string $resource): bool
    {
        if ($this->usesBlocksEditor) {
            return true;
        }

        $view = $this->viewFor($resource);

        return is_string($view) && $view !== '' && view()->exists($view);
    }

    /**
     * @return array{id:string,label:string,description:string,storage:string,view:?string,uses_blocks_editor:bool,sort_order:int}
     */
    public function toArray(string $resource): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'description' => $this->description,
            'storage' => $this->storage,
            'view' => $this->viewFor($resource),
            'uses_blocks_editor' => $this->usesBlocksEditor,
            'sort_order' => $this->sortOrder,
        ];
    }
}
