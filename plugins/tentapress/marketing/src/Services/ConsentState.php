<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Services;

use Illuminate\Http\Request;

final readonly class ConsentState
{
    public function __construct(private MarketingSettings $settings)
    {
    }

    /**
     * @return array{analytics?:bool,updated_at?:string}|null
     */
    public function payload(?Request $request = null): ?array
    {
        $request ??= request();

        $raw = $request->cookie($this->settings->cookieName());

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function hasDecision(?Request $request = null): bool
    {
        $payload = $this->payload($request);

        return is_array($payload) && array_key_exists('analytics', $payload);
    }

    public function analyticsAllowed(?Request $request = null): bool
    {
        $payload = $this->payload($request);

        return is_array($payload) && ($payload['analytics'] ?? false) === true;
    }
}
