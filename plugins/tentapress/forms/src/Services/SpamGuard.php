<?php

declare(strict_types=1);

namespace TentaPress\Forms\Services;

final class SpamGuard
{
    public function firstFailureMessage(string $honeypotValue, int $startedAt, int $now): ?string
    {
        if (trim($honeypotValue) !== '') {
            return 'We could not process your submission.';
        }

        if ($startedAt <= 0) {
            return 'Please refresh and try again.';
        }

        $elapsed = $now - $startedAt;

        if ($elapsed < 2) {
            return 'Please wait a moment before submitting.';
        }

        return null;
    }
}
