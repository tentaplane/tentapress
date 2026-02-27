<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use TentaPress\Forms\Destinations\DestinationRegistry;
use TentaPress\Forms\Destinations\KitDestination;
use TentaPress\Forms\Services\FormConfigNormalizer;
use TentaPress\Forms\Services\FormPayloadSigner;
use TentaPress\Forms\Services\FormSubmissionService;
use TentaPress\Forms\Services\SpamGuard;

it('emits structured diagnostics when provider submission fails', function (): void {
    Log::spy();

    $signer = resolve(FormPayloadSigner::class);
    $registry = new DestinationRegistry();
    $registry->register(new KitDestination());

    $service = new FormSubmissionService(
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
        ],
        'kit_form_id' => '12345',
        'error_message' => 'Unable to submit right now',
    ]);

    $outcome = $service->submit([
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        'email' => 'person@example.test',
    ], 'telemetry-check');

    expect($outcome->ok)->toBeFalse();

    Log::assertLogged('info', fn(string $message, array $context): bool => $message === 'forms.submission.result'
        && ($context['provider'] ?? null) === 'kit'
        && ($context['ok'] ?? null) === false
        && ($context['failure_category'] ?? null) === 'configuration'
        && is_string($context['attempt_id'] ?? null)
        && ($context['attempt_id'] ?? '') !== '');

    Log::assertLogged('warning', fn(string $message, array $context): bool => $message === 'forms.submission.failed'
        && ($context['provider'] ?? null) === 'kit'
        && ($context['failure_category'] ?? null) === 'configuration'
        && is_string($context['attempt_id'] ?? null)
        && ($context['attempt_id'] ?? '') !== '');
});
