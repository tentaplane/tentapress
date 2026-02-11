<?php

declare(strict_types=1);

use TentaPress\System\Support\Paths;

it('writes plugin cache entries that match the plugin cache contract', function (): void {
    $fixturePath = __DIR__.'/../Fixtures/plugin-cache-contract.json';
    $fixture = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

    $pluginId = (string) ($fixture['required_plugin_id'] ?? '');
    $entryKeys = is_array($fixture['entry_keys'] ?? null) ? array_values($fixture['entry_keys']) : [];
    $manifestKeys = is_array($fixture['manifest_keys'] ?? null) ? array_values($fixture['manifest_keys']) : [];

    expect($pluginId)->not->toBe('');
    expect($entryKeys)->not->toBeEmpty();
    expect($manifestKeys)->not->toBeEmpty();

    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable '.$pluginId)->assertSuccessful();

    $this->artisan('tp:plugins cache')
        ->expectsOutputToContain('Plugin cache rebuilt.')
        ->assertSuccessful();

    $cachePath = Paths::pluginCachePath();

    expect(is_file($cachePath))->toBeTrue();

    $cachePayload = require $cachePath;

    expect(is_array($cachePayload))->toBeTrue();
    expect(is_array($cachePayload['plugins'] ?? null))->toBeTrue();
    expect(array_key_exists($pluginId, (array) $cachePayload['plugins']))->toBeTrue();

    $entry = (array) $cachePayload['plugins'][$pluginId];

    expect(array_keys($entry))->toEqualCanonicalizing($entryKeys);

    $manifest = (array) ($entry['manifest'] ?? []);

    foreach ($manifestKeys as $key) {
        expect(array_key_exists((string) $key, $manifest))->toBeTrue();
    }

    expect((string) ($manifest['id'] ?? ''))->toBe($pluginId);
});
