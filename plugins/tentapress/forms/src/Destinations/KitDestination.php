<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

use Illuminate\Support\Facades\Http;

final class KitDestination implements SubmissionDestination
{
    public function key(): string
    {
        return 'kit';
    }

    public function submit(array $providerConfig, array $fieldValues, array $fieldDefinitions, array $context = []): DestinationResult
    {
        $apiKey = trim((string) ($providerConfig['api_key'] ?? ''));
        $formId = trim((string) ($providerConfig['form_id'] ?? ''));
        $tagId = trim((string) ($providerConfig['tag_id'] ?? ''));

        if ($apiKey === '') {
            return new DestinationResult(ok: false, error: 'Kit api_key is required.');
        }

        if ($formId === '') {
            return new DestinationResult(ok: false, error: 'Kit form_id is required.');
        }

        $email = $this->resolveEmail($fieldValues, $fieldDefinitions);

        if ($email === null) {
            return new DestinationResult(ok: false, error: 'Kit requires an email field.');
        }

        $baseUrl = rtrim(trim((string) ($providerConfig['base_url'] ?? 'https://api.kit.com')), '/');

        if (! $this->isValidHttpUrl($baseUrl)) {
            return new DestinationResult(ok: false, error: 'Kit base URL is invalid.');
        }

        $firstName = $this->resolveFirstName($fieldValues);
        $customFields = $this->customFields($fieldValues, $fieldDefinitions);
        $request = Http::acceptJson()
            ->asJson()
            ->timeout(10)
            ->withHeader('X-Kit-Api-Key', $apiKey);

        $createPayload = [
            'email_address' => $email,
        ];

        if ($firstName !== null) {
            $createPayload['first_name'] = $firstName;
        }

        if ($customFields !== []) {
            $createPayload['fields'] = $customFields;
        }

        try {
            $createResponse = $request->post($baseUrl.'/v4/subscribers', $createPayload);
        } catch (\Throwable $exception) {
            return new DestinationResult(ok: false, error: $exception->getMessage());
        }

        if (! $createResponse->successful()) {
            return new DestinationResult(ok: false, statusCode: $createResponse->status(), error: 'Kit rejected subscriber creation.');
        }

        $addToFormPayload = [
            'email_address' => $email,
        ];

        $referrer = trim((string) ($context['source_url'] ?? ''));
        if ($referrer !== '' && $this->isValidHttpUrl($referrer)) {
            $addToFormPayload['referrer'] = $referrer;
        }

        try {
            $formResponse = $request->post($baseUrl.'/v4/forms/'.rawurlencode($formId).'/subscribers', $addToFormPayload);
        } catch (\Throwable $exception) {
            return new DestinationResult(ok: false, error: $exception->getMessage());
        }

        if (! $formResponse->successful()) {
            return new DestinationResult(ok: false, statusCode: $formResponse->status(), error: 'Kit rejected adding subscriber to form.');
        }

        if ($tagId !== '') {
            try {
                $tagResponse = $request->post($baseUrl.'/v4/tags/'.rawurlencode($tagId).'/subscribers', [
                    'email_address' => $email,
                ]);
            } catch (\Throwable $exception) {
                return new DestinationResult(ok: false, error: $exception->getMessage());
            }

            if (! $tagResponse->successful()) {
                return new DestinationResult(ok: false, statusCode: $tagResponse->status(), error: 'Kit rejected subscriber tagging.');
            }
        }

        return new DestinationResult(ok: true, statusCode: $formResponse->status());
    }

    /**
     * @param  array<string,mixed>  $fieldValues
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fieldDefinitions
     */
    private function resolveEmail(array $fieldValues, array $fieldDefinitions): ?string
    {
        foreach ($fieldDefinitions as $field) {
            if (($field['type'] ?? '') !== 'email') {
                continue;
            }

            $key = (string) ($field['key'] ?? '');
            $email = strtolower(trim((string) ($fieldValues[$key] ?? '')));

            if ($email !== '') {
                return $email;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $fieldValues
     */
    private function resolveFirstName(array $fieldValues): ?string
    {
        foreach (['first_name', 'firstname', 'fname'] as $key) {
            $value = trim((string) ($fieldValues[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $fieldValues
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fieldDefinitions
     * @return array<string,string>
     */
    private function customFields(array $fieldValues, array $fieldDefinitions): array
    {
        $fields = [];

        foreach ($fieldDefinitions as $definition) {
            $key = (string) ($definition['key'] ?? '');
            $type = (string) ($definition['type'] ?? 'text');

            if ($key === '' || $type === 'email' || ! array_key_exists($key, $fieldValues)) {
                continue;
            }

            $value = $fieldValues[$key];
            $text = trim((string) $value);

            if ($text === '') {
                continue;
            }

            $fields[$key] = $text;
        }

        return $fields;
    }

    private function isValidHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
