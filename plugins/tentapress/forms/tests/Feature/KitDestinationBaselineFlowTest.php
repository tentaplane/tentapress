<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use TentaPress\Forms\Destinations\DestinationRegistry;
use TentaPress\Forms\Destinations\KitDestination;
use TentaPress\Forms\Services\FormConfigNormalizer;
use TentaPress\Forms\Services\FormPayloadSigner;
use TentaPress\Forms\Services\FormSubmissionService;
use TentaPress\Forms\Services\SpamGuard;

it('submits to kit through the form submission service', function (): void {
    Http::fake([
        'https://api.kit.com/v4/subscribers' => Http::response(['id' => 123], 201),
        'https://api.kit.com/v4/forms/*/subscribers' => Http::response(['id' => 123], 201),
        'https://api.kit.com/v4/tags/*/subscribers' => Http::response(['id' => 123], 201),
    ]);

    $signer = app(FormPayloadSigner::class);
    $registry = new DestinationRegistry();
    $registry->register(new KitDestination());

    $submissionService = new FormSubmissionService(
        signer: $signer,
        normalizer: new FormConfigNormalizer(),
        spamGuard: new SpamGuard(),
        destinations: $registry,
    );

    $payload = $signer->sign([
        'provider' => 'kit',
        'fields' => [
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,
                'placeholder' => '',
                'default' => '',
                'options' => '',
                'merge_tag' => 'EMAIL',
            ],
            [
                'key' => 'first_name',
                'label' => 'First Name',
                'type' => 'text',
                'required' => false,
                'placeholder' => '',
                'default' => '',
                'options' => '',
                'merge_tag' => '',
            ],
        ],
        'kit_api_key' => 'kit_api_key_123',
        'kit_form_id' => '5678901',
        'kit_tag_id' => '43210',
        'success_message' => 'Submitted',
        'error_message' => 'Submission failed',
    ]);

    $outcome = $submissionService->submit([
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => 'https://example.test/contact#form',
        'email' => 'kit@example.test',
        'first_name' => 'Taylor',
    ], 'kit-form');

    expect($outcome->ok)->toBeTrue();
    expect($outcome->message)->toBe('Submitted');

    Http::assertSentCount(3);
    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        return $request->hasHeader('X-Kit-Api-Key', 'kit_api_key_123');
    });
});
