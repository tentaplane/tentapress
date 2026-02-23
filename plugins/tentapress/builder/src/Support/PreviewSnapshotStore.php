<?php

declare(strict_types=1);

namespace TentaPress\Builder\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class PreviewSnapshotStore
{
    private const TTL_SECONDS = 600;

    /**
     * @param  array<string,mixed>  $payload
     */
    public function put(int $userId, array $payload): string
    {
        $token = Str::random(48);

        Cache::put($this->cacheKey($token), [
            'user_id' => $userId,
            'payload' => $payload,
        ], now()->addSeconds(self::TTL_SECONDS));

        return $token;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function get(string $token, int $userId): ?array
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        $stored = Cache::get($this->cacheKey($token));

        if (! is_array($stored)) {
            return null;
        }

        $ownerId = (int) ($stored['user_id'] ?? 0);

        if ($ownerId <= 0 || $ownerId !== $userId) {
            return null;
        }

        $payload = $stored['payload'] ?? null;

        return is_array($payload) ? $payload : null;
    }

    private function cacheKey(string $token): string
    {
        return 'tp.builder.preview.'.$token;
    }
}
