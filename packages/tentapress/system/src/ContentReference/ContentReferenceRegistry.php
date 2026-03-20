<?php

declare(strict_types=1);

namespace TentaPress\System\ContentReference;

final class ContentReferenceRegistry
{
    /**
     * @var array<string,ContentReferenceSource>
     */
    private array $sources = [];

    public function register(ContentReferenceSource $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    /**
     * @param  array<int,string>  $allowedSources
     * @param  array<string,array<string,mixed>>  $constraintsBySource
     * @return array<int,ContentReference>
     */
    public function options(array $allowedSources = [], array $constraintsBySource = []): array
    {
        $references = [];

        foreach ($this->sources as $key => $source) {
            if ($allowedSources !== [] && ! in_array($key, $allowedSources, true)) {
                continue;
            }

            foreach ($source->options($constraintsBySource[$key] ?? []) as $reference) {
                $references[] = $reference;
            }
        }

        usort($references, static fn (ContentReference $left, ContentReference $right): int => strcasecmp($left->title, $right->title));

        return array_values($references);
    }

    public function find(string $source, string $id): ?ContentReference
    {
        $referenceSource = $this->sources[$source] ?? null;

        if (! $referenceSource instanceof ContentReferenceSource) {
            return null;
        }

        return $referenceSource->find($id);
    }
}
