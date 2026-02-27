<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use TentaPress\Forms\Destinations\KitDestination;

it('returns an error when required kit config is missing', function (): void {
    $destination = new KitDestination();

    $result = $destination->submit(
        providerConfig: ['api_key' => 'kit_api_key_123'],
        fieldValues: ['email' => 'kit@example.test'],
        fieldDefinitions: [
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                'placeholder' => '',
                'default' => '',
                'options' => [],
                'merge_tag' => 'EMAIL',
            ],
        ],
    );

    expect($result->ok)->toBeFalse();
    expect($result->error)->toBe('Kit form_id is required.');
});

it('returns failure when kit rejects adding subscriber to form', function (): void {
    Http::fake([
        'https://api.kit.com/v4/subscribers' => Http::response(['id' => 123], 201),
        'https://api.kit.com/v4/forms/*/subscribers' => Http::response(['message' => 'Invalid form'], 400),
    ]);

    $destination = new KitDestination();

    $result = $destination->submit(
        providerConfig: [
            'api_key' => 'kit_api_key_123',
            'form_id' => '5678901',
        ],
        fieldValues: [
            'email' => 'kit@example.test',
        ],
        fieldDefinitions: [
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                'placeholder' => '',
                'default' => '',
                'options' => [],
                'merge_tag' => 'EMAIL',
            ],
        ],
    );

    expect($result->ok)->toBeFalse();
    expect($result->statusCode)->toBe(400);
    expect($result->error)->toBe('Kit rejected adding subscriber to form.');
});
