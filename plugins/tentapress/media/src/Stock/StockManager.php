<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

final readonly class StockManager
{
    public function __construct(private StockSourceRegistry $registry)
    {
    }

    /**
     * @return StockSource[]
     */
    public function all(): array
    {
        return $this->registry->all();
    }

    /**
     * @return StockSource[]
     */
    public function enabled(): array
    {
        return $this->registry->enabled();
    }

    public function get(string $key): ?StockSource
    {
        return $this->registry->get($key);
    }
}
