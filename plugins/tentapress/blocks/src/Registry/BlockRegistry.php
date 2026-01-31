<?php

declare(strict_types=1);

namespace TentaPress\Blocks\Registry;

final class BlockRegistry
{
    private array $definitions = [];

    public function register(BlockDefinition $definition): void
    {
        $this->definitions[$definition->type] = $definition;
    }

    /**
     * @return array<int,BlockDefinition>
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    public function get(string $type): ?BlockDefinition
    {
        return $this->definitions[$type] ?? null;
    }
}
