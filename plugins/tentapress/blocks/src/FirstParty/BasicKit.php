<?php

declare(strict_types=1);

namespace TentaPress\Blocks\FirstParty;

use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final class BasicKit
{
    public function register(BlockRegistry $registry): void
    {
        $definitionsPath = __DIR__.'/../../resources/definitions';

        if (! is_dir($definitionsPath)) {
            return;
        }

        $files = glob($definitionsPath.'/*.json') ?: [];
        sort($files);

        foreach ($files as $file) {
            $definition = $this->definitionFromFile($file);

            if (! $definition) {
                continue;
            }

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
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        $type = isset($data['type']) ? trim((string) $data['type']) : '';
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $description = isset($data['description']) ? trim((string) $data['description']) : '';

        if ($type === '' || $name === '' || $description === '') {
            return null;
        }

        $fields = is_array($data['fields'] ?? null) ? $data['fields'] : [];
        $defaults = is_array($data['defaults'] ?? null) ? $data['defaults'] : [];
        $example = is_array($data['example'] ?? null) ? $data['example'] : [];
        $variants = is_array($data['variants'] ?? null) ? $data['variants'] : [];
        $view = isset($data['view']) ? (string) $data['view'] : null;
        $version = isset($data['version']) ? (int) $data['version'] : 1;
        $defaultVariant = isset($data['default_variant']) ? (string) $data['default_variant'] : null;

        return new BlockDefinition(
            type: $type,
            name: $name,
            description: $description,
            version: $version > 0 ? $version : 1,
            fields: $fields,
            defaults: $defaults,
            example: $example,
            view: $view,
            variants: $variants,
            defaultVariant: $defaultVariant !== '' ? $defaultVariant : null,
        );
    }
}
