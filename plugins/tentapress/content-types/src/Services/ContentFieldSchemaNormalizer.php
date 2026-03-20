<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Str;
use RuntimeException;

final class ContentFieldSchemaNormalizer
{
    /**
     * @var array<int,string>
     */
    private array $allowedTypes = [
        'text',
        'textarea',
        'number',
        'boolean',
        'date_time',
        'select',
        'relation',
    ];

    /**
     * @param  mixed  $raw
     * @return array<int,array{key:string,label:string,field_type:string,sort_order:int,required:bool,config:array<string,mixed>}>
     */
    public function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $fields = [];
        $keys = [];

        foreach ($raw as $field) {
            if (! is_array($field)) {
                continue;
            }

            $key = Str::snake(trim((string) ($field['key'] ?? '')));
            $label = trim((string) ($field['label'] ?? ''));
            $fieldType = trim((string) ($field['field_type'] ?? ''));
            $required = filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOL);

            throw_if($key === '', RuntimeException::class, 'Every field must have a key.');
            throw_if(! preg_match('/^[a-z][a-z0-9_]*$/', $key), RuntimeException::class, "Field key '{$key}' must use lowercase snake_case.");
            throw_if(isset($keys[$key]), RuntimeException::class, "Field key '{$key}' is duplicated.");
            throw_if($label === '', RuntimeException::class, "Field '{$key}' must have a label.");
            throw_if(! in_array($fieldType, $this->allowedTypes, true), RuntimeException::class, "Field '{$key}' uses an unsupported type.");

            $keys[$key] = true;
            $fields[] = [
                'key' => $key,
                'label' => $label,
                'field_type' => $fieldType,
                'sort_order' => count($fields) + 1,
                'required' => $required,
                'config' => $this->normalizeConfig($fieldType, $field['config'] ?? []),
            ];
        }

        return $fields;
    }

    /**
     * @param  mixed  $raw
     * @return array<string,mixed>
     */
    private function normalizeConfig(string $fieldType, mixed $raw): array
    {
        $config = is_array($raw) ? $raw : [];

        if ($fieldType === 'select') {
            $options = [];

            foreach ($config['options'] ?? [] as $option) {
                if (! is_array($option)) {
                    continue;
                }

                $value = trim((string) ($option['value'] ?? ''));
                $label = trim((string) ($option['label'] ?? ''));

                if ($value === '' || $label === '') {
                    continue;
                }

                $options[] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }

            throw_if($options === [], RuntimeException::class, 'Select fields must include at least one option.');

            return ['options' => $options];
        }

        if ($fieldType === 'relation') {
            $sources = [];
            $typeKeys = [];

            foreach ($config['allowed_sources'] ?? ['content-types'] as $source) {
                $normalizedSource = trim((string) $source);

                if ($normalizedSource === '') {
                    continue;
                }

                $sources[] = $normalizedSource;
            }

            if ($sources === []) {
                $sources[] = 'content-types';
            }

            foreach ($config['allowed_type_keys'] ?? [] as $typeKey) {
                $normalized = Str::slug((string) $typeKey);

                if ($normalized === '') {
                    continue;
                }

                $typeKeys[] = $normalized;
            }

            return [
                'allowed_sources' => array_values(array_unique($sources)),
                'allowed_type_keys' => array_values(array_unique($typeKeys)),
            ];
        }

        return [];
    }
}
