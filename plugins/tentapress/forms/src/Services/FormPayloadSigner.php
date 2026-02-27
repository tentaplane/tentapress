<?php

declare(strict_types=1);

namespace TentaPress\Forms\Services;

use Illuminate\Support\Facades\Crypt;

final class FormPayloadSigner
{
    /**
     * @param  array<string,mixed>  $payload
     */
    public function sign(array $payload): string
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return Crypt::encryptString($json);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function verify(string $token): ?array
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        try {
            $json = Crypt::decryptString($token);
        } catch (\Throwable) {
            return null;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
