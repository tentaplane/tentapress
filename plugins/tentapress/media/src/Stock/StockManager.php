<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

use TentaPress\Media\Stock\Sources\PexelsSource;
use TentaPress\Media\Stock\Sources\UnsplashSource;

final class StockManager
{
    /**
     * @var array<string,StockSource>
     */
    private array $sources = [];

    public function __construct(private readonly StockSettings $settings)
    {
        $this->sources = [
            'unsplash' => new UnsplashSource($this->settings),
            'pexels' => new PexelsSource($this->settings),
        ];
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
