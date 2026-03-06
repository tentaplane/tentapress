<?php

declare(strict_types=1);

use TentaPress\System\Support\ComposerPackageRequirement;

it('formats first-party package requirements with the expected pre-1.0 dev constraint', function (): void {
    expect(ComposerPackageRequirement::forRequire('tentapress/redirects'))->toBe('tentapress/redirects:<1.0@dev');
});

it('leaves third-party package requirements unchanged', function (): void {
    expect(ComposerPackageRequirement::forRequire('vendor/package'))->toBe('vendor/package');
});
