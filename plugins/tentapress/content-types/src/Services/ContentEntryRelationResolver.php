<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use TentaPress\System\ContentReference\ContentReference;
use TentaPress\System\ContentReference\ContentReferenceRegistry;

final readonly class ContentEntryRelationResolver
{
    public function __construct(
        private ContentReferenceRegistry $references,
    ) {
    }

    public function find(mixed $value): ?ContentReference
    {
        if (is_int($value) || (is_string($value) && preg_match('/^[1-9][0-9]*$/', $value) === 1)) {
            return $this->references->find('content-types', (string) $value);
        }

        $reference = trim((string) $value);

        if ($reference === '' || ! str_contains($reference, ':')) {
            return null;
        }

        [$source, $id] = explode(':', $reference, 2);

        $source = trim($source);
        $id = trim($id);

        if ($source === '' || $id === '') {
            return null;
        }

        return $this->references->find($source, $id);
    }

    /**
     * @param  array<int,string>  $allowedSources
     * @param  array<string,array<string,mixed>>  $constraintsBySource
     * @return array<int,ContentReference>
     */
    public function options(array $allowedSources = ['content-types'], array $constraintsBySource = []): array
    {
        return $this->references->options($allowedSources, $constraintsBySource);
    }

    public function canonicalValue(mixed $value): ?string
    {
        return $this->find($value)?->value();
    }
}
