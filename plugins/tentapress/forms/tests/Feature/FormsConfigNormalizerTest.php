<?php

declare(strict_types=1);

use TentaPress\Forms\Services\FormConfigNormalizer;

it('normalizes kit and convertkit providers', function (): void {
    $normalizer = new FormConfigNormalizer();

    expect($normalizer->normalizeProvider('kit'))->toBe('kit');
    expect($normalizer->normalizeProvider('convertkit'))->toBe('kit');
});

it('maps kit provider config props without overriding explicit provider_config values', function (): void {
    $normalizer = new FormConfigNormalizer();

    $config = $normalizer->normalizeProviderConfig([
        'kit_api_key' => 'from-field-key',
        'kit_form_id' => 'from-field-form',
        'kit_tag_id' => 'from-field-tag',
        'provider_config' => [
            'api_key' => 'from-provider-config',
            'form_id' => 'from-provider-config-form',
        ],
    ]);

    expect($config['api_key'])->toBe('from-provider-config');
    expect($config['form_id'])->toBe('from-provider-config-form');
    expect($config['tag_id'])->toBe('from-field-tag');
});
