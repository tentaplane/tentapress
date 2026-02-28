<?php

declare(strict_types=1);

use TentaPress\System\Plugin\PluginAssetRegistry;

it('serves stable admin-shell asset filenames with runtime cache busting', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/admin-shell')->assertSuccessful();

    $tags = resolve(PluginAssetRegistry::class)->tags('tentapress/admin-shell');

    expect($tags)->toContain('plugins/tentapress/admin-shell/build/admin-styles.css?v=');
    expect($tags)->toContain('plugins/tentapress/admin-shell/build/admin.js?v=');
});
