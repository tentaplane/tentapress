<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

final readonly class TaxonomyDefinition
{
    /**
     * @param  array<string,mixed>  $config
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $singularLabel,
        public ?string $description = null,
        public bool $isHierarchical = false,
        public bool $isPublic = true,
        public array $config = [],
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toPersistencePayload(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'singular_label' => $this->singularLabel,
            'description' => $this->description,
            'is_hierarchical' => $this->isHierarchical,
            'is_public' => $this->isPublic,
            'config' => $this->config,
        ];
    }
}
