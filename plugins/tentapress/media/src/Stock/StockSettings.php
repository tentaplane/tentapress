<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

use TentaPress\Settings\Services\SettingsStore;

final readonly class StockSettings
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function unsplashEnabled(): bool
    {
        return $this->settings->get('stock.unsplash.enabled', '1') === '1';
    }

    public function unsplashKey(): string
    {
        return (string) $this->settings->get('stock.unsplash.key', '');
    }

    public function pexelsEnabled(): bool
    {
        return $this->settings->get('stock.pexels.enabled', '1') === '1';
    }

    public function pexelsKey(): string
    {
        return (string) $this->settings->get('stock.pexels.key', '');
    }

    public function pexelsVideoEnabled(): bool
    {
        return $this->settings->get('stock.pexels.video_enabled', '1') === '1';
    }

    public function attributionReminderEnabled(): bool
    {
        return $this->settings->get('stock.attribution.reminder', '1') === '1';
    }
}
