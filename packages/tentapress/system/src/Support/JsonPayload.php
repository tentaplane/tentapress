<?php

declare(strict_types=1);

namespace TentaPress\System\Support;

final class JsonPayload
{
    public function encode(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    /**
     * @return array<string,mixed>|null
     */
    public function decode(string $raw): ?array
    {
        if (trim($raw) === '') {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function decodeOrEmpty(string $raw): array
    {
        return $this->decode($raw) ?? [];
    }
}
