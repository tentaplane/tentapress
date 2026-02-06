<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

interface StockSource
{
    public function key(): string;

    public function label(): string;

    /**
     * @return string[]
     */
    public function supportedMediaTypes(): array;

    public function isEnabled(): bool;

    public function search(StockQuery $query): StockSearchResults;

    public function find(string $id, ?string $mediaType = null): ?StockResult;
}
