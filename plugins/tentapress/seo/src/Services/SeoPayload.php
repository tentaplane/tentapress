<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

final class SeoPayload
{
    public function nullIfEmpty(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<int,string> $keys
     */
    public function isEmpty(array $payload, array $keys): bool
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return false;
            }

            if ($value !== null && !is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
