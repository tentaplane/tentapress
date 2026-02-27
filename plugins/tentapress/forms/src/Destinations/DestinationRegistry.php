<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

final class DestinationRegistry
{
    /**
     * @var array<string,SubmissionDestination>
     */
    private array $items = [];

    public function register(SubmissionDestination $destination): void
    {
        $this->items[$destination->key()] = $destination;
    }

    public function get(string $key): ?SubmissionDestination
    {
        return $this->items[$key] ?? null;
    }
}
