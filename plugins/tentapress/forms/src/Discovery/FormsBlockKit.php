<?php

declare(strict_types=1);

namespace TentaPress\Forms\Discovery;

use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final class FormsBlockKit
{
    public function register(BlockRegistry $registry): void
    {
        $definition = $this->definitionFromFile(__DIR__.'/../../resources/definitions/forms-signup.json');

        if ($definition instanceof BlockDefinition) {
            $registry->register($definition);
        }
    }

    private function definitionFromFile(string $path): ?BlockDefinition
    {
        $raw = file_get_contents($path);

        if ($raw === false) {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        $type = trim((string) ($data['type'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($type === '' || $name === '' || $description === '') {
            return null;
        }

        return new BlockDefinition(
            type: $type,
            name: $name,
            description: $description,
            version: max(1, (int) ($data['version'] ?? 1)),
            fields: is_array($data['fields'] ?? null) ? $data['fields'] : [],
            defaults: is_array($data['defaults'] ?? null) ? $data['defaults'] : [],
            example: is_array($data['example'] ?? null) ? $data['example'] : [],
            view: isset($data['view']) ? (string) $data['view'] : null,
            variants: is_array($data['variants'] ?? null) ? $data['variants'] : [],
            defaultVariant: isset($data['default_variant']) ? (string) $data['default_variant'] : null,
        );
    }
}
