<?php

declare(strict_types=1);

it('fails when enabling without a plugin id', function (): void {
    $this->artisan('tp:plugins enable')
        ->expectsOutputToContain('Missing plugin id. Use: php artisan tp:plugins enable vendor/name OR add --all')
        ->assertFailed();
});

it('fails when disabling a protected plugin without force', function (): void {
    $this->artisan('tp:plugins disable tentapress/users')
        ->expectsOutputToContain("Refusing to disable protected plugin 'tentapress/users'. Use --force to override.")
        ->assertFailed();
});

it('fails when enabling an unknown plugin id', function (): void {
    $this->artisan('tp:plugins enable acme/unknown')
        ->expectsOutputToContain('Plugin not found in tp_plugins: acme/unknown. Did you run `php artisan tp:plugins sync`?')
        ->assertFailed();
});
