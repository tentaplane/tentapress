<?php

declare(strict_types=1);

namespace TentaPress\Forms\Services;

final class FormConfigNormalizer
{
    /**
     * @return array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>
     */
    public function normalizeFields(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($raw)) {
            return [];
        }

        $allowedTypes = ['email', 'text', 'textarea', 'checkbox', 'select', 'hidden'];
        $out = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $rawKey = trim((string) ($item['key'] ?? ''));
            $key = strtolower((string) preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rawKey));
            $key = trim($key, '_-');

            $label = trim((string) ($item['label'] ?? ''));
            $type = strtolower(trim((string) ($item['type'] ?? 'text')));

            if ($key === '' || $label === '') {
                continue;
            }

            if (! in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $out[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => $this->isTruthy($item['required'] ?? false),
                'placeholder' => (string) ($item['placeholder'] ?? ''),
                'default' => (string) ($item['default'] ?? ''),
                'options' => $this->normalizeOptions($item['options'] ?? []),
                'merge_tag' => strtoupper(trim((string) ($item['merge_tag'] ?? ''))),
            ];
        }

        return $out;
    }

    public function normalizeProvider(mixed $raw): string
    {
        $value = strtolower(trim((string) $raw));

        return match ($value) {
            'tentaforms', 'tentafor.ms' => 'tentafor.ms',
            'kit', 'convertkit' => 'kit',
            default => 'mailchimp',
        };
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function normalizeProviderConfig(array $payload): array
    {
        $base = $payload['provider_config'] ?? [];

        if (is_string($base)) {
            $decoded = json_decode($base, true);
            $base = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($base)) {
            $base = [];
        }

        $mailchimpActionUrl = trim((string) ($payload['mailchimp_action_url'] ?? ''));
        $mailchimpListId = trim((string) ($payload['mailchimp_list_id'] ?? ''));
        $mailchimpGdprTag = trim((string) ($payload['mailchimp_gdpr_tag'] ?? ''));

        if ($mailchimpActionUrl !== '' && ! array_key_exists('action_url', $base)) {
            $base['action_url'] = $mailchimpActionUrl;
        }

        if ($mailchimpListId !== '' && ! array_key_exists('list_id', $base)) {
            $base['list_id'] = $mailchimpListId;
        }

        if ($mailchimpGdprTag !== '' && ! array_key_exists('gdpr_tag', $base)) {
            $base['gdpr_tag'] = $mailchimpGdprTag;
        }

        $tentaFormsId = trim((string) ($payload['tentaforms_form_id'] ?? ''));
        $tentaFormsEnvironment = trim((string) ($payload['tentaforms_environment'] ?? ''));

        if ($tentaFormsId !== '' && ! array_key_exists('form_id', $base)) {
            $base['form_id'] = $tentaFormsId;
        }

        if ($tentaFormsEnvironment !== '' && ! array_key_exists('environment', $base)) {
            $base['environment'] = $tentaFormsEnvironment;
        }

        $kitApiKey = trim((string) ($payload['kit_api_key'] ?? ''));
        $kitFormId = trim((string) ($payload['kit_form_id'] ?? ''));
        $kitTagId = trim((string) ($payload['kit_tag_id'] ?? ''));

        if ($kitApiKey !== '' && ! array_key_exists('api_key', $base)) {
            $base['api_key'] = $kitApiKey;
        }

        if ($kitFormId !== '' && ! array_key_exists('form_id', $base)) {
            $base['form_id'] = $kitFormId;
        }

        if ($kitTagId !== '' && ! array_key_exists('tag_id', $base)) {
            $base['tag_id'] = $kitTagId;
        }

        return $base;
    }

    /**
     * @return array<string,string>
     */
    private function normalizeOptions(mixed $raw): array
    {
        if (is_array($raw)) {
            $out = [];

            foreach ($raw as $item) {
                if (is_array($item)) {
                    $value = trim((string) ($item['value'] ?? ''));
                    $label = trim((string) ($item['label'] ?? $value));
                } else {
                    $value = trim((string) $item);
                    $label = $value;
                }

                if ($value === '') {
                    continue;
                }

                $out[$value] = $label;
            }

            return $out;
        }

        $rawString = trim((string) $raw);

        if ($rawString === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/', $rawString) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $parts = array_map(trim(...), explode('|', $line, 2));
            $value = (string) ($parts[0] ?? '');
            $label = (string) ($parts[1] ?? $value);

            if ($value === '') {
                continue;
            }

            $out[$value] = $label;
        }

        return $out;
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $text = strtolower(trim((string) $value));

        return in_array($text, ['1', 'true', 'yes', 'on'], true);
    }
}
