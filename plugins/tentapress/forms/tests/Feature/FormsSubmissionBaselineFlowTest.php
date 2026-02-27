<?php

declare(strict_types=1);

use TentaPress\Forms\Services\FormPayloadSigner;

it('submits successfully and stores success status in session', function (): void {
    $signer = resolve(FormPayloadSigner::class);

    $payload = $signer->sign([
        'provider' => 'tentafor.ms',
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
        'tentaforms_form_id' => 'newsletter',
        'success_message' => 'Thanks for joining',
    ]);

    $response = $this->post('/forms/submit/newsletter', [
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => '/thanks',
        'email' => 'person@example.test',
    ]);

    $response->assertRedirect('/thanks');
    $response->assertSessionHas('tp_forms.status.newsletter.type', 'success');
    $response->assertSessionHas('tp_forms.status.newsletter.message', 'Thanks for joining');
});

it('allows same-host absolute return urls', function (): void {
    $signer = resolve(FormPayloadSigner::class);

    $payload = $signer->sign([
        'provider' => 'tentafor.ms',
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
        'tentaforms_form_id' => 'newsletter',
    ]);

    $returnUrl = url('/newsletter#signup');

    $response = $this->post('/forms/submit/newsletter', [
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => $returnUrl,
        'email' => 'person@example.test',
    ]);

    $response->assertRedirect($returnUrl);
});
