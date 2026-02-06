<?php

declare(strict_types=1);

namespace TentaPress\Media\Optimization;

use TentaPress\Settings\Services\SettingsStore;

final readonly class OptimizationSettings
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function enabled(): bool
    {
        return $this->settings->get('optimization.enabled', '0') === '1';
    }

    public function provider(): string
    {
        return (string) $this->settings->get('optimization.provider', '');
    }
}
