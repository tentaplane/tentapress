<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use TentaPress\Forms\Destinations\MailchimpDestination;

it('reports success when mailchimp returns a successful status code', function (): void {
    Http::fake([
        'https://example.us1.list-manage.com/subscribe/post*' => Http::response('ok', 200),
    ]);

    $destination = new MailchimpDestination();

    $result = $destination->submit(
        providerConfig: [
            'action_url' => 'https://example.us1.list-manage.com/subscribe/post?u=abc&id=def',
        ],
        fieldValues: [
            'email' => 'person@example.test',
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

    expect($result->ok)->toBeTrue();
    expect($result->statusCode)->toBe(200);
});

it('reports failure when mailchimp returns a non-success status code', function (): void {
    Http::fake([
        'https://example.us1.list-manage.com/subscribe/post*' => Http::response('bad request', 400),
    ]);

    $destination = new MailchimpDestination();

    $result = $destination->submit(
        providerConfig: [
            'action_url' => 'https://example.us1.list-manage.com/subscribe/post?u=abc&id=def',
        ],
        fieldValues: [
            'email' => 'person@example.test',
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
    expect($result->error)->toBe('Mailchimp rejected the submission.');
});
