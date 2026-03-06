<?php

declare(strict_types=1);

use TentaPress\SystemInfo\Models\TpPluginInstall;

it('builds the manual install command with the first-party package constraint', function (): void {
    $attempt = new TpPluginInstall([
        'package' => 'tentapress/redirects',
    ]);

    expect($attempt->manualCommand())->toBe('composer require tentapress/redirects:<1.0@dev');
});

it('leaves third-party manual install commands unchanged', function (): void {
    $attempt = new TpPluginInstall([
        'package' => 'vendor/package',
    ]);

    expect($attempt->manualCommand())->toBe('composer require vendor/package');
});
