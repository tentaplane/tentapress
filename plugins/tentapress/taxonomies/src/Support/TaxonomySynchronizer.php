<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

use TentaPress\Taxonomies\Models\TpTaxonomy;

final readonly class TaxonomySynchronizer
{
    public function __construct(
        private TaxonomyRegistry $registry,
    ) {
    }

    public function syncRegistered(): int
    {
        $definitions = $this->registry->all();

        if ($definitions === []) {
            return 0;
        }

        $now = now();
        $rows = [];

        foreach ($definitions as $definition) {
            $payload = $definition->toPersistencePayload();

            $rows[] = [
                ...$payload,
                'config' => json_encode($payload['config'], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        TpTaxonomy::query()->upsert(
            $rows,
            ['key'],
            ['label', 'singular_label', 'description', 'is_hierarchical', 'is_public', 'config', 'updated_at']
        );

        return count($rows);
    }
}
