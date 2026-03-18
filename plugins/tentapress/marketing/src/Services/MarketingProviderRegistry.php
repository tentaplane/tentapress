<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Services;

use TentaPress\Marketing\Contracts\MarketingProvider;

final class MarketingProviderRegistry
{
    /**
     * @var array<string,MarketingProvider>
     */
    private array $providers = [];

    public function register(MarketingProvider $provider): void
    {
        $this->providers[$provider->key()] = $provider;
    }

    /**
     * @return array<string,MarketingProvider>
     */
    public function all(): array
    {
        ksort($this->providers);

        return $this->providers;
    }
}
