<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

final class ComposerPackageRequirement
{
    private const FIRST_PARTY_REQUIRE_CONSTRAINT = '<1.0@dev';

    public static function forRequire(string $package): string
    {
        $normalizedPackage = trim(strtolower($package));

        if ($normalizedPackage === '') {
            return '';
        }

        if (! str_starts_with($normalizedPackage, 'tentapress/')) {
            return $normalizedPackage;
        }

        return $normalizedPackage . ':' . self::FIRST_PARTY_REQUIRE_CONSTRAINT;
    }
}
