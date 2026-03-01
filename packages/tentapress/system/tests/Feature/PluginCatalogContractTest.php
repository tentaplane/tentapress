<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use TentaPress\System\Catalog\FirstPartyPluginCatalogGenerator;

it('keeps the committed first-party plugin catalog in sync with plugin manifests', function (): void {
    $catalogPath = base_path('docs/catalog/first-party-plugins.json');

    expect(is_file($catalogPath))->toBeTrue();

    $generated = resolve(FirstPartyPluginCatalogGenerator::class)->generate();
    $committed = json_decode((string) file_get_contents($catalogPath), true, 512, JSON_THROW_ON_ERROR);

    unset($generated['generated_at'], $committed['generated_at']);

    expect($committed)->toBe($generated);
});

it('generates the first-party plugin catalog from plugin manifests', function (): void {
    $catalogPath = storage_path('framework/testing/plugin-catalog-'.uniqid('', true).'.json');

    $this->artisan('tp:catalog', ['action' => 'generate', '--path' => $catalogPath])
        ->expectsOutputToContain('Generated first-party plugin catalog')
        ->assertSuccessful();

    $payload = json_decode((string) file_get_contents($catalogPath), true, 512, JSON_THROW_ON_ERROR);

    expect((int) ($payload['schema_version'] ?? 0))->toBe(1);
    expect($payload['plugins'])->toBeArray();
    expect(collect($payload['plugins'])->firstWhere('id', 'tentapress/admin-shell')['latest_version'] ?? null)
        ->toBe(json_decode((string) file_get_contents(base_path('plugins/tentapress/admin-shell/tentapress.json')), true, 512, JSON_THROW_ON_ERROR)['version']);

    File::delete($catalogPath);
});

it('passes the catalog drift check when the committed catalog is current', function (): void {
    $this->artisan('tp:catalog', ['action' => 'check'])
        ->expectsOutputToContain('First-party plugin catalog is up to date.')
        ->assertSuccessful();
});
