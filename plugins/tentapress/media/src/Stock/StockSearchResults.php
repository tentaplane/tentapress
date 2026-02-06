<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

final readonly class StockSearchResults
{
    /**
     * @param StockResult[] $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $perPage,
        public ?int $total,
        public bool $hasMore,
    ) {
    }
}
