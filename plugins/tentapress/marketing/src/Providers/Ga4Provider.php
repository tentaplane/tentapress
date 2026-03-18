<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Providers;

use TentaPress\Marketing\Contracts\MarketingProvider;
use TentaPress\Marketing\Services\MarketingSettings;

final class Ga4Provider implements MarketingProvider
{
    public function key(): string
    {
        return 'ga4';
    }

    public function label(): string
    {
        return 'Google Analytics 4';
    }

    public function description(): string
    {
        return 'Google tag (gtag.js) integration using a GA4 measurement ID.';
    }

    public function fields(): array
    {
        return [
            [
                'key' => 'measurement_id',
                'label' => 'Measurement ID',
                'help' => 'Example: G-XXXXXXXXXX',
                'placeholder' => 'G-XXXXXXXXXX',
                'default' => '',
                'required' => true,
                'max' => 64,
            ],
        ];
    }

    public function isConfigured(MarketingSettings $settings): bool
    {
        return $settings->providerValue($this->key(), 'measurement_id') !== '';
    }

    public function render(MarketingSettings $settings): array
    {
        $measurementId = $settings->providerValue($this->key(), 'measurement_id');

        if ($measurementId === '') {
            return [];
        }

        return [
            'head' => <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$measurementId}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$measurementId}');
</script>
HTML,
        ];
    }
}
