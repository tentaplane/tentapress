<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves core smoke routes without server errors', function (): void {
    $upResponse = $this->get('/up');
    $adminLoginResponse = $this->get('/admin/login');
    $homeResponse = $this->get('/');

    expect($upResponse->getStatusCode())->toBeLessThan(500);
    expect($adminLoginResponse->getStatusCode())->toBeLessThan(500);
    expect($homeResponse->getStatusCode())->toBeLessThan(500);
});

it('redirects guest admin dashboard access to login', function (): void {
    $response = $this->get('/admin');

    expect($response->getStatusCode())->toBeLessThan(500);
    $response->assertRedirect('/admin/login');
});
