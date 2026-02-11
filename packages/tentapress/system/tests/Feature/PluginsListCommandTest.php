<?php

declare(strict_types=1);

it('shows an empty-state message when no plugins are synced', function (): void {
    $this->artisan('tp:plugins list')
        ->expectsOutputToContain('No plugins found. Run: php artisan tp:plugins sync')
        ->assertSuccessful();
});
