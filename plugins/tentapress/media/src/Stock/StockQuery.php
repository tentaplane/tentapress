<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

final readonly class StockQuery
{
    public function __construct(
        public string $query,
        public ?string $mediaType,
        public ?string $orientation,
        public ?string $sort,
        public int $page = 1,
        public int $perPage = 24,
    ) {
    }
}
