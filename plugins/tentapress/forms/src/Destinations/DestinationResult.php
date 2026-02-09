<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

final readonly class DestinationResult
{
    public function __construct(
        public bool $ok,
        public ?int $statusCode = null,
        public ?string $error = null,
    ) {
    }
}
