<?php

declare(strict_types=1);

namespace TentaPress\System\ContentReference;

final readonly class ContentReference
{
    public function __construct(
        public string $source,
        public string $id,
        public string $title,
        public string $typeLabel,
        public array $meta = [],
    ) {
    }

    public function value(): string
    {
        return $this->source.':'.$this->id;
    }
}
