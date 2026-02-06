<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

final class StockSourceRegistry
{
    /**
     * @var array<string,StockSource>
     */
    private array $sources = [];

    public function register(StockSource $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    /**
     * @return StockSource[]
     */
    public function all(): array
    {
        return array_values($this->sources);
    }

    /**
     * @return StockSource[]
     */
    public function enabled(): array
    {
        return array_values(array_filter(
            $this->sources,
            static fn (StockSource $source): bool => $source->isEnabled()
        ));
    }

    public function get(string $key): ?StockSource
    {
        return $this->sources[$key] ?? null;
    }
}
