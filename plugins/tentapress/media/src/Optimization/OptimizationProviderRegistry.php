<?php

declare(strict_types=1);

namespace TentaPress\Media\Optimization;

final class OptimizationProviderRegistry
{
    /**
     * @var array<string,OptimizationProvider>
     */
    private array $providers = [];

    public function register(OptimizationProvider $provider): void
    {
        $this->providers[$provider->key()] = $provider;
    }

    /**
     * @return OptimizationProvider[]
     */
    public function all(): array
    {
        return array_values($this->providers);
    }

    /**
     * @return OptimizationProvider[]
     */
    public function enabled(): array
    {
        return array_values(array_filter(
            $this->providers,
            static fn (OptimizationProvider $provider): bool => $provider->isEnabled()
        ));
    }

    public function get(string $key): ?OptimizationProvider
    {
        return $this->providers[$key] ?? null;
    }
}
