<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

final class RedirectPathNormalizer
{
    public function normalizeSourcePath(string $value): string
    {
        return $this->normalizePathValue($value, allowExternal: false);
    }

    public function normalizeTargetPath(string $value): string
    {
        return $this->normalizePathValue($value, allowExternal: false);
    }

    private function normalizePathValue(string $value, bool $allowExternal): string
    {
        $value = trim($value);

        throw_if($value === '', InvalidArgumentException::class, 'Path cannot be empty.');

        if (Str::startsWith($value, ['http://', 'https://'])) {
            throw_unless($allowExternal, InvalidArgumentException::class, 'Only relative paths are supported.');

            $parsedPath = (string) (parse_url($value, PHP_URL_PATH) ?? '');
            $value = $parsedPath !== '' ? $parsedPath : '/';
        }

        $value = '/'.ltrim($value, '/');
        $value = '/'.trim(preg_replace('#/+#', '/', $value) ?? '/', '/');

        return $value === '/' ? '/' : rtrim($value, '/');
    }
}
