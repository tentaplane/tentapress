<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

use InvalidArgumentException;

final class TaxonomyRegistry
{
    /**
     * @var array<string,TaxonomyDefinition>
     */
    private array $definitions = [];

    public function register(TaxonomyDefinition $definition): void
    {
        $key = trim($definition->key);

        throw_if($key === '', InvalidArgumentException::class, 'Taxonomy key cannot be empty.');

        throw_if(isset($this->definitions[$key]), InvalidArgumentException::class, "Taxonomy [{$key}] is already registered.");

        $this->definitions[$key] = $definition;
    }

    public function has(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function find(string $key): ?TaxonomyDefinition
    {
        return $this->definitions[$key] ?? null;
    }

    /**
     * @return array<string,TaxonomyDefinition>
     */
    public function all(): array
    {
        return $this->definitions;
    }
}
