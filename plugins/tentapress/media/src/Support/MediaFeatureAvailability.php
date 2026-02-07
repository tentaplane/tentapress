<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use TentaPress\System\Plugin\PluginRegistry;

final readonly class MediaFeatureAvailability
{
    public function __construct(
        private PluginRegistry $plugins,
    ) {
    }

    public function hasStockSources(): bool
    {
        foreach (array_keys($this->plugins->readCache()) as $pluginId) {
            if (str_starts_with($pluginId, 'tentapress/media-stock-')) {
                return true;
            }
        }

        return false;
    }

    public function hasOptimizationProviders(): bool
    {
        foreach (array_keys($this->plugins->readCache()) as $pluginId) {
            if (str_starts_with($pluginId, 'tentapress/media-optimization-')) {
                return true;
            }
        }

        return false;
    }

    public function isEnabled(string $pluginId): bool
    {
        return isset($this->plugins->readCache()[$pluginId]);
    }
}
