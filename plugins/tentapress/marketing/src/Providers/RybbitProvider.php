<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Providers;

use TentaPress\Marketing\Contracts\MarketingProvider;
use TentaPress\Marketing\Services\MarketingSettings;

final class RybbitProvider implements MarketingProvider
{
    public function key(): string
    {
        return 'rybbit';
    }

    public function label(): string
    {
        return 'Rybbit';
    }

    public function description(): string
    {
        return 'Rybbit tracking script using a site ID and optional self-hosted script URL.';
    }

    public function fields(): array
    {
        return [
            [
                'key' => 'site_id',
                'label' => 'Site ID',
                'help' => 'The site identifier from your Rybbit dashboard.',
                'placeholder' => 'your-site-id',
                'default' => '',
                'required' => true,
                'max' => 255,
            ],
            [
                'key' => 'script_url',
                'label' => 'Script URL',
                'help' => 'Override only when self-hosting Rybbit.',
                'placeholder' => 'https://app.rybbit.io/api/script.js',
                'default' => 'https://app.rybbit.io/api/script.js',
                'required' => false,
                'max' => 500,
            ],
        ];
    }

    public function isConfigured(MarketingSettings $settings): bool
    {
        return $settings->providerValue($this->key(), 'site_id') !== '';
    }

    public function render(MarketingSettings $settings): array
    {
        $siteId = $settings->providerValue($this->key(), 'site_id');
        $scriptUrl = $settings->providerValue($this->key(), 'script_url', 'https://app.rybbit.io/api/script.js');

        if ($siteId === '' || $scriptUrl === '') {
            return [];
        }

        return [
            'head' => <<<HTML
<script src="{$scriptUrl}" async data-site-id="{$siteId}"></script>
HTML,
        ];
    }
}
