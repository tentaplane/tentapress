<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes plugin orchestration commands from the root test harness', function (): void {
    $this->artisan('list')
        ->expectsOutputToContain('tp:plugins')
        ->assertSuccessful();
});

it('runs plugin list command against the test database', function (): void {
    $this->artisan('tp:plugins list')->assertSuccessful();
});
