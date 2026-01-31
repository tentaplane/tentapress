<?php

declare(strict_types=1);

namespace TentaPress\Blocks\Registry;

final readonly class BlockDefinition
{
    /**
     * @param  array<int,array<string,mixed>>  $fields
     * @param  array<string,mixed>  $defaults
     * @param  array<string,mixed>  $example
     * @param  array<int,array<string,mixed>>  $variants
     */
    public function __construct(
        public string $type,
        public string $name,
        public string $description,
        public int $version,
        public array $fields,
        public array $defaults = [],
        public array $example = [],
        public ?string $view = null, // optional override for view key
        public array $variants = [],
        public ?string $defaultVariant = null,
    ) {
    }
}
