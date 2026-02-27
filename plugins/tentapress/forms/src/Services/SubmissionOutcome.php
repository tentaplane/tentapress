<?php

declare(strict_types=1);

namespace TentaPress\Forms\Services;

final readonly class SubmissionOutcome
{
    public function __construct(
        public bool $ok,
        public string $message,
        public ?string $redirectUrl = null,
    ) {
    }
}
