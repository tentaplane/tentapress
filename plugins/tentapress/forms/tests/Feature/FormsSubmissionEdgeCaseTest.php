<?php

declare(strict_types=1);

use TentaPress\Forms\Services\FormPayloadSigner;

it('returns validation errors when required payload is missing', function (): void {
    $response = $this->from('/contact')->post('/forms/submit/newsletter', [
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        'email' => 'person@example.test',
    ]);

    $response->assertRedirect('/contact');
    $response->assertSessionHasErrors(['_tp_payload']);
});

it('returns validation errors when required email field is missing', function (): void {
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

    $response = $this->from('/contact')->post('/forms/submit/newsletter', [
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => '/contact',
    ]);

    $response->assertRedirect('/contact');
    $response->assertSessionHasErrors(['email']);
});

it('rejects too-fast submissions with spam guard message', function (): void {
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

    $response = $this->post('/forms/submit/newsletter', [
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => '/contact',
        'email' => 'person@example.test',
    ]);

    $response->assertRedirect('/contact');
    $response->assertSessionHas('tp_forms.status.newsletter.type', 'error');
    $response->assertSessionHas('tp_forms.status.newsletter.message', 'Please wait a moment before submitting.');
});

it('does not follow cross-host return urls', function (): void {
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

    $response = $this->from('/contact')->post('/forms/submit/newsletter', [
        '_tp_payload' => $payload,
        '_tp_started_at' => now()->subSeconds(3)->timestamp,
        '_tp_hp' => '',
        '_tp_return_url' => 'https://evil.example/phish',
        'email' => 'person@example.test',
    ]);

    $response->assertRedirect('/contact');
});
