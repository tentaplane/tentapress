<?php

declare(strict_types=1);

namespace TentaPress\System\ContentReference;

interface ContentReferenceSource
{
    public function key(): string;

    /**
     * @param  array<string,mixed>  $constraints
     * @return array<int,ContentReference>
     */
    public function options(array $constraints = []): array;

    public function find(string $id): ?ContentReference;
}
