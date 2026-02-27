<?php

declare(strict_types=1);

namespace TentaPress\Forms\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use TentaPress\Forms\Destinations\DestinationRegistry;

final readonly class FormSubmissionService
{
    public function __construct(
        private FormPayloadSigner $signer,
        private FormConfigNormalizer $normalizer,
        private SpamGuard $spamGuard,
        private DestinationRegistry $destinations,
    ) {
    }

    /**
     * @param  array<string,mixed>  $input
     */
    public function submit(array $input, string $formKey): SubmissionOutcome
    {
        $payload = $this->signer->verify((string) ($input['_tp_payload'] ?? ''));

        if (! is_array($payload)) {
            return new SubmissionOutcome(false, 'Form configuration is invalid. Please refresh and try again.');
        }

        $spamFailure = $this->spamGuard->firstFailureMessage(
            honeypotValue: (string) ($input['_tp_hp'] ?? ''),
            startedAt: (int) ($input['_tp_started_at'] ?? 0),
            now: now()->timestamp,
        );

        if ($spamFailure !== null) {
            return new SubmissionOutcome(false, $spamFailure);
        }

        $fields = $this->normalizer->normalizeFields($payload['fields'] ?? []);

        if ($fields === []) {
            return new SubmissionOutcome(false, 'No fields were configured for this form.');
        }

        $validated = Validator::make(
            $input,
            $this->rulesForFields($fields),
            [],
            $this->attributesForFields($fields),
        )->validate();

        $fieldValues = $this->fieldValues($fields, $validated);

        $provider = $this->normalizer->normalizeProvider($payload['provider'] ?? 'mailchimp');
        $providerConfig = $this->normalizer->normalizeProviderConfig($payload);
        $destination = $this->destinations->get($provider);

        if ($destination === null) {
            return new SubmissionOutcome(false, 'Submission provider is not available.');
        }

        $result = $destination->submit(
            providerConfig: $providerConfig,
            fieldValues: $fieldValues,
            fieldDefinitions: $fields,
            context: [
                'form_key' => $formKey,
                'source_url' => (string) ($input['_tp_return_url'] ?? ''),
            ],
        );

        $emailHash = $this->emailHash($fieldValues, $fields);

        logger()->info('Forms submission attempt', [
            'form_key' => $formKey,
            'provider' => $provider,
            'ok' => $result->ok,
            'status_code' => $result->statusCode,
            'error' => $result->error,
            'field_count' => count($fieldValues),
            'email_hash' => $emailHash,
        ]);

        if (! $result->ok) {
            $errorMessage = trim((string) ($payload['error_message'] ?? ''));

            return new SubmissionOutcome(false, $errorMessage !== '' ? $errorMessage : 'We could not submit your form. Please try again.');
        }

        $successMessage = trim((string) ($payload['success_message'] ?? ''));
        $redirectUrl = trim((string) ($payload['redirect_url'] ?? ''));

        return new SubmissionOutcome(
            true,
            $successMessage !== '' ? $successMessage : 'Thanks. Your submission was received.',
            $redirectUrl !== '' ? $redirectUrl : null,
        );
    }

    /**
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fields
     * @return array<string,array<int,mixed>>
     */
    private function rulesForFields(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $type = $field['type'];
            $required = $field['required'];

            if ($type === 'checkbox') {
                $rules[$key] = $required ? ['accepted'] : ['nullable', 'boolean'];

                continue;
            }

            $fieldRules = ['bail', $required ? 'required' : 'nullable', 'string'];

            if ($type === 'email') {
                $fieldRules[] = 'email:rfc';
            }

            if ($type === 'textarea') {
                $fieldRules[] = 'max:5000';
            } else {
                $fieldRules[] = 'max:255';
            }

            if ($type === 'select' && $field['options'] !== []) {
                $fieldRules[] = Rule::in(array_keys($field['options']));
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fields
     * @return array<string,string>
     */
    private function attributesForFields(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            $attributes[$field['key']] = $field['label'];
        }

        return $attributes;
    }

    /**
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fields
     * @param  array<string,mixed>  $validated
     * @return array<string,mixed>
     */
    private function fieldValues(array $fields, array $validated): array
    {
        $values = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $type = $field['type'];

            if ($type === 'checkbox') {
                $values[$key] = $this->isTruthy($validated[$key] ?? false);

                continue;
            }

            $value = $validated[$key] ?? $field['default'];
            $values[$key] = is_string($value) ? trim($value) : (string) $value;
        }

        return $values;
    }

    /**
     * @param  array<string,mixed>  $fieldValues
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fields
     */
    private function emailHash(array $fieldValues, array $fields): ?string
    {
        foreach ($fields as $field) {
            if ($field['type'] !== 'email') {
                continue;
            }

            $key = $field['key'];
            $email = strtolower(trim((string) ($fieldValues[$key] ?? '')));

            if ($email === '') {
                continue;
            }

            return hash('sha256', $email);
        }

        return null;
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
