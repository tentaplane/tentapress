<?php

declare(strict_types=1);

namespace TentaPress\Forms\Destinations;

use Illuminate\Support\Facades\Http;

final class MailchimpDestination implements SubmissionDestination
{
    public function key(): string
    {
        return 'mailchimp';
    }

    public function submit(array $providerConfig, array $fieldValues, array $fieldDefinitions, array $context = []): DestinationResult
    {
        $actionUrl = trim((string) ($providerConfig['action_url'] ?? ''));

        if (! $this->isValidHttpUrl($actionUrl)) {
            return new DestinationResult(ok: false, error: 'Mailchimp action URL is invalid.');
        }

        $payload = $this->mapPayload($providerConfig, $fieldValues, $fieldDefinitions);

        if ($payload === []) {
            return new DestinationResult(ok: false, error: 'No fields were mapped for Mailchimp.');
        }

        $listId = trim((string) ($providerConfig['list_id'] ?? ''));

        if ($listId !== '' && ! array_key_exists('id', $payload)) {
            $payload['id'] = $listId;
        }

        try {
            $response = Http::asForm()
                ->accept('*/*')
                ->timeout(10)
                ->post($actionUrl, $payload);
        } catch (\Throwable $exception) {
            return new DestinationResult(ok: false, error: $exception->getMessage());
        }

        $status = $response->status();

        if ($status < 200 || $status >= 400) {
            return new DestinationResult(ok: false, statusCode: $status, error: 'Mailchimp rejected the submission.');
        }

        return new DestinationResult(ok: true, statusCode: $status);
    }

    /**
     * @param  array<string,mixed>  $providerConfig
     * @param  array<string,mixed>  $fieldValues
     * @param  array<int,array{key:string,label:string,type:string,required:bool,placeholder:string,default:string,options:array<string,string>,merge_tag:string}>  $fieldDefinitions
     * @return array<string,string>
     */
    private function mapPayload(array $providerConfig, array $fieldValues, array $fieldDefinitions): array
    {
        $payload = [];

        foreach ($fieldDefinitions as $definition) {
            $key = (string) ($definition['key'] ?? '');

            if ($key === '' || ! array_key_exists($key, $fieldValues)) {
                continue;
            }

            $value = $fieldValues[$key];
            $type = (string) ($definition['type'] ?? 'text');

            if ($type === 'checkbox') {
                if (! $this->isTruthy($value)) {
                    continue;
                }

                $value = '1';
            }

            $mergeTag = $this->resolveMergeTag($definition);

            if ($mergeTag === '') {
                continue;
            }

            $payload[$mergeTag] = trim((string) $value);
        }

        $gdprTag = strtoupper(trim((string) ($providerConfig['gdpr_tag'] ?? '')));
        if ($gdprTag !== '' && ! array_key_exists($gdprTag, $payload)) {
            foreach ($fieldDefinitions as $definition) {
                if (($definition['type'] ?? '') !== 'checkbox') {
                    continue;
                }

                $checkboxKey = (string) ($definition['key'] ?? '');
                if ($checkboxKey === '') {
                    continue;
                }

                if ($this->isTruthy($fieldValues[$checkboxKey] ?? false)) {
                    $payload[$gdprTag] = '1';
                    break;
                }
            }
        }

        return $payload;
    }

    /**
     * @param  array{key:string,merge_tag:string}  $definition
     */
    private function resolveMergeTag(array $definition): string
    {
        $explicit = strtoupper(trim((string) ($definition['merge_tag'] ?? '')));

        if ($explicit !== '') {
            return $explicit;
        }

        $key = strtolower(trim((string) ($definition['key'] ?? '')));

        return match ($key) {
            'email', 'email_address' => 'EMAIL',
            'first_name', 'firstname', 'fname' => 'FNAME',
            'last_name', 'lastname', 'lname' => 'LNAME',
            default => substr((string) preg_replace('/[^A-Z0-9]/', '', strtoupper($key)), 0, 10),
        };
    }

    private function isValidHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
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
