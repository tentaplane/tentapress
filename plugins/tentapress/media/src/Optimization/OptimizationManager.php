<?php

declare(strict_types=1);

namespace TentaPress\Media\Optimization;

final readonly class OptimizationManager
{
    public function __construct(
        private OptimizationProviderRegistry $registry,
        private OptimizationSettings $settings,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->settings->enabled();
    }

    public function activeProvider(): ?OptimizationProvider
    {
        $providerKey = $this->settings->provider();

        if ($providerKey === '') {
            return null;
        }

        return $this->registry->get($providerKey);
    }

    /**
     * @param array<string, scalar> $params
     */
    public function imageUrl(string $sourceUrl, array $params = []): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $provider = $this->activeProvider();

        if ($provider === null || ! $provider->isEnabled()) {
            return null;
        }

        $normalized = array_filter(
            $params,
            static fn ($value): bool => $value !== null && $value !== ''
        );

        return $provider->imageUrl($sourceUrl, $normalized);
    }
}
