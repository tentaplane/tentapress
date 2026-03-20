<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\Date;
use RuntimeException;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Models\TpContentTypeField;

final readonly class ContentEntryFieldValueNormalizer
{
    public function __construct(
        private ContentEntryRelationResolver $relations,
    ) {
    }

    /**
     * @param  array<string,mixed>  $rawValues
     * @return array<string,mixed>
     */
    public function normalize(TpContentType $contentType, array $rawValues): array
    {
        $normalized = [];

        foreach ($contentType->fields as $field) {
            if (! $field instanceof TpContentTypeField) {
                continue;
            }

            $value = $rawValues[$field->key] ?? null;
            $normalized[$field->key] = $this->normalizeFieldValue($field, $value);
        }

        return $normalized;
    }

    private function normalizeFieldValue(TpContentTypeField $field, mixed $value): mixed
    {
        if ($value === '' || $value === null) {
            throw_if($field->required, RuntimeException::class, "Field '{$field->label}' is required.");

            return null;
        }

        return match ((string) $field->field_type) {
            'text', 'textarea' => $this->normalizeString($field, $value),
            'number' => $this->normalizeNumber($field, $value),
            'boolean' => $this->normalizeBoolean($value),
            'date_time' => $this->normalizeDateTime($field, $value),
            'select' => $this->normalizeSelect($field, $value),
            'relation' => $this->normalizeRelation($field, $value),
            default => null,
        };
    }

    private function normalizeString(TpContentTypeField $field, mixed $value): string
    {
        $string = trim((string) $value);

        throw_if($string === '' && $field->required, RuntimeException::class, "Field '{$field->label}' is required.");

        return $string;
    }

    private function normalizeNumber(TpContentTypeField $field, mixed $value): int|float
    {
        throw_if(! is_numeric($value), RuntimeException::class, "Field '{$field->label}' must be numeric.");

        $number = (string) $value;

        if (str_contains($number, '.')) {
            return (float) $number;
        }

        return (int) $number;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    private function normalizeDateTime(TpContentTypeField $field, mixed $value): string
    {
        try {
            return Date::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            throw new RuntimeException("Field '{$field->label}' must be a valid date and time.");
        }
    }

    private function normalizeSelect(TpContentTypeField $field, mixed $value): string
    {
        $string = trim((string) $value);
        $options = collect($field->config['options'] ?? [])
            ->map(fn (mixed $option): string => trim((string) (($option['value'] ?? ''))))
            ->filter()
            ->values()
            ->all();

        throw_if(! in_array($string, $options, true), RuntimeException::class, "Field '{$field->label}' must use a valid option.");

        return $string;
    }

    private function normalizeRelation(TpContentTypeField $field, mixed $value): int
    {
        $entryId = (int) $value;

        throw_if($entryId <= 0, RuntimeException::class, "Field '{$field->label}' must reference a valid content entry.");

        $entry = $this->relations->find($entryId);

        throw_if($entry === null, RuntimeException::class, "Field '{$field->label}' must reference an existing content entry.");

        $allowedTypeKeys = collect($field->config['allowed_type_keys'] ?? [])
            ->map(fn (mixed $typeKey): string => trim((string) $typeKey))
            ->filter()
            ->values()
            ->all();

        if ($allowedTypeKeys !== []) {
            $entryTypeKey = (string) ($entry->contentType?->key ?? '');

            throw_if(
                ! in_array($entryTypeKey, $allowedTypeKeys, true),
                RuntimeException::class,
                "Field '{$field->label}' must reference an allowed content type."
            );
        }

        return $entryId;
    }
}
