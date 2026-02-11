<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('syncs plugin manifests into the plugins table', function (): void {
    $this->artisan('tp:plugins sync')
        ->expectsOutputToContain('Synced ')
        ->expectsOutputToContain('Plugin cache rebuilt.')
        ->assertSuccessful();

    $pluginIds = DB::table('tp_plugins')
        ->pluck('id')
        ->map(static fn (mixed $id): string => (string) $id)
        ->all();

    expect($pluginIds)->toContain('tentapress/pages');
});

it('lists synced plugins after a successful sync', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();

    $this->artisan('tp:plugins list')
        ->expectsOutputToContain('tentapress/pages')
        ->assertSuccessful();
});
