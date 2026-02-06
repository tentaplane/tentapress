<?php

declare(strict_types=1);

namespace TentaPress\Media\Optimization;

interface OptimizationProvider
{
    public function key(): string;

    public function label(): string;

    public function isEnabled(): bool;

    /**
     * @param array<string, scalar> $params
     */
    public function imageUrl(string $sourceUrl, array $params = []): ?string;
}
