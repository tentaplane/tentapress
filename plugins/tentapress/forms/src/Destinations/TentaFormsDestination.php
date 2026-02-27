<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

use Illuminate\Support\Facades\Http;

final class TentaFormsDestination implements SubmissionDestination
{
    public function key(): string
    {
        return 'tentafor.ms';
    }

    public function submit(array $providerConfig, array $fieldValues, array $fieldDefinitions, array $context = []): DestinationResult
    {
        $formId = trim((string) ($providerConfig['form_id'] ?? ''));

        if ($formId === '') {
            return new DestinationResult(ok: false, error: 'TentaForms form_id is required.');
        }

        $stub = array_key_exists('stub', $providerConfig)
            ? $this->isTruthy($providerConfig['stub'])
            : $this->defaultStub();

        if ($stub) {
            return new DestinationResult(ok: true, statusCode: 202);
        }

        $environment = strtolower(trim((string) ($providerConfig['environment'] ?? 'production')));
        $defaultBase = $environment === 'staging' ? 'https://staging-api.tentaforms.com' : 'https://api.tentaforms.com';
        $baseUrl = rtrim(trim((string) ($providerConfig['base_url'] ?? $defaultBase)), '/');

        if (! $this->isValidHttpUrl($baseUrl)) {
            return new DestinationResult(ok: false, error: 'TentaForms base URL is invalid.');
        }

        $endpoint = $baseUrl.'/forms/'.rawurlencode($formId).'/submissions';

        $request = Http::acceptJson()->asJson()->timeout(10);
        $apiKey = trim((string) ($providerConfig['api_key'] ?? ''));

        if ($apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        try {
            $response = $request->post($endpoint, [
                'fields' => $fieldValues,
                'context' => [
                    'form_key' => (string) ($context['form_key'] ?? ''),
                    'source_url' => (string) ($context['source_url'] ?? ''),
                ],
            ]);
        } catch (\Throwable $exception) {
            return new DestinationResult(ok: false, error: $exception->getMessage());
        }

        if (! $response->successful()) {
            return new DestinationResult(ok: false, statusCode: $response->status(), error: 'TentaForms rejected the submission.');
        }

        return new DestinationResult(ok: true, statusCode: $response->status());
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

    private function isValidHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function defaultStub(): bool
    {
        return app()->environment(['local', 'testing']);
    }
}
