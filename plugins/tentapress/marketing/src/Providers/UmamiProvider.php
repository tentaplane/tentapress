<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Providers;

use TentaPress\Marketing\Contracts\MarketingProvider;
use TentaPress\Marketing\Services\MarketingSettings;

final class UmamiProvider implements MarketingProvider
{
    public function key(): string
    {
        return 'umami';
    }

    public function label(): string
    {
        return 'Umami';
    }

    public function description(): string
    {
        return 'Umami tracker script with configurable website ID and script URL.';
    }

    public function fields(): array
    {
        return [
            [
                'key' => 'website_id',
                'label' => 'Website ID',
                'help' => 'The website identifier from your Umami dashboard.',
                'placeholder' => '94db1cb1-74f4-4a40-ad6c-962362670409',
                'default' => '',
                'required' => true,
                'max' => 255,
            ],
            [
                'key' => 'script_url',
                'label' => 'Script URL',
                'help' => 'Use your self-hosted script URL or keep the managed Umami Cloud default.',
                'placeholder' => 'https://cloud.umami.is/script.js',
                'default' => 'https://cloud.umami.is/script.js',
                'required' => false,
                'max' => 500,
            ],
        ];
    }

    public function isConfigured(MarketingSettings $settings): bool
    {
        return $settings->providerValue($this->key(), 'website_id') !== '';
    }

    public function render(MarketingSettings $settings): array
    {
        $websiteId = $settings->providerValue($this->key(), 'website_id');
        $scriptUrl = $settings->providerValue($this->key(), 'script_url', 'https://cloud.umami.is/script.js');

        if ($websiteId === '' || $scriptUrl === '') {
            return [];
        }

        return [
            'head' => <<<HTML
<script defer src="{$scriptUrl}" data-website-id="{$websiteId}"></script>
HTML,
        ];
    }
}
