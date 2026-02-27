<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use TentaPress\Forms\Destinations\TentaFormsDestination;

it('defaults tentaforms destination to stub mode in testing', function (): void {
    Http::fake();

    $destination = new TentaFormsDestination();

    $result = $destination->submit(
        providerConfig: [
            'form_id' => 'contact-us',
        ],
        fieldValues: [
            'email' => 'person@example.test',
        ],
        fieldDefinitions: [],
    );

    expect($result->ok)->toBeTrue();
    expect($result->statusCode)->toBe(202);
    Http::assertNothingSent();
});

it('allows explicit non-stub behavior when configured', function (): void {
    Http::fake([
        'https://api.tentaforms.com/forms/*/submissions' => Http::response(['ok' => true], 201),
    ]);

    $destination = new TentaFormsDestination();

    $result = $destination->submit(
        providerConfig: [
            'form_id' => 'contact-us',
            'stub' => false,
            'environment' => 'production',
        ],
        fieldValues: [
            'email' => 'person@example.test',
        ],
        fieldDefinitions: [],
        context: [
            'form_key' => 'contact',
            'source_url' => 'https://example.test/contact',
        ],
    );

    expect($result->ok)->toBeTrue();
    expect($result->statusCode)->toBe(201);
    Http::assertSentCount(1);
});
