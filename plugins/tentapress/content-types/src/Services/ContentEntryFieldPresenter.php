<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\Date;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Models\TpContentTypeField;

final readonly class ContentEntryFieldPresenter
{
    public function __construct(
        private ContentEntryRelationResolver $relations,
    ) {
    }

    /**
     * @param  array<string,mixed>  $fieldValues
     * @return array<int,array{key:string,label:string,field_type:string,value:mixed,display:string}>
     */
    public function present(TpContentType $contentType, array $fieldValues): array
    {
        $presented = [];

        foreach ($contentType->fields as $field) {
            if (! $field instanceof TpContentTypeField) {
                continue;
            }

            $value = $fieldValues[$field->key] ?? null;

            $presented[] = [
                'key' => (string) $field->key,
                'label' => (string) $field->label,
                'field_type' => (string) $field->field_type,
                'value' => $value,
                'display' => $this->displayValue($field, $value),
            ];
        }

        return $presented;
    }

    private function displayValue(TpContentTypeField $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ((string) $field->field_type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL) ? 'Yes' : 'No',
            'date_time' => Date::parse((string) $value)->format('j M Y H:i'),
            'relation' => $this->displayRelation($value),
            'select' => $this->displaySelect($field, (string) $value),
            default => (string) $value,
        };
    }

    private function displayRelation(mixed $value): string
    {
        $entry = $this->relations->find($value);

        return $entry?->title ?? (string) $value;
    }

    private function displaySelect(TpContentTypeField $field, string $value): string
    {
        foreach ($field->config['options'] ?? [] as $option) {
            if (! is_array($option)) {
                continue;
            }

            if ((string) ($option['value'] ?? '') === $value) {
                return (string) ($option['label'] ?? $value);
            }
        }

        return $value;
    }
}
