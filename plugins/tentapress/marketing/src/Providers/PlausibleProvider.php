<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Providers;

use TentaPress\Marketing\Contracts\MarketingProvider;
use TentaPress\Marketing\Services\MarketingSettings;

final class PlausibleProvider implements MarketingProvider
{
    public function key(): string
    {
        return 'plausible';
    }

    public function label(): string
    {
        return 'Plausible';
    }

    public function description(): string
    {
        return 'Privacy-friendly Plausible Analytics script with configurable script URL.';
    }

    public function fields(): array
    {
        return [
            [
                'key' => 'domain',
                'label' => 'Domain',
                'help' => 'The domain configured in Plausible.',
                'placeholder' => 'example.com',
                'default' => '',
                'required' => true,
                'max' => 255,
            ],
            [
                'key' => 'script_url',
                'label' => 'Script URL',
                'help' => 'Override only when self-hosting or proxying the Plausible script.',
                'placeholder' => 'https://plausible.io/js/script.js',
                'default' => 'https://plausible.io/js/script.js',
                'required' => false,
                'max' => 500,
            ],
        ];
    }

    public function isConfigured(MarketingSettings $settings): bool
    {
        return $settings->providerValue($this->key(), 'domain') !== '';
    }

    public function render(MarketingSettings $settings): array
    {
        $domain = $settings->providerValue($this->key(), 'domain');
        $scriptUrl = $settings->providerValue($this->key(), 'script_url', 'https://plausible.io/js/script.js');

        if ($domain === '' || $scriptUrl === '') {
            return [];
        }

        return [
            'head' => <<<HTML
<script defer data-domain="{$domain}" src="{$scriptUrl}"></script>
HTML,
        ];
    }
}
