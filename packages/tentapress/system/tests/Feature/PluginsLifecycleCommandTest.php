<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use TentaPress\System\Support\Paths;

it('enables configured default plugins when running defaults action', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();

    DB::table('tp_plugins')->update([
        'enabled' => 0,
        'updated_at' => now(),
    ]);

    $this->artisan('tp:plugins defaults')
        ->expectsOutputToContain('Enabled ')
        ->expectsOutputToContain('Plugin cache rebuilt.')
        ->assertSuccessful();

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/users')->value('enabled')
    )->toBe(1);

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/pages')->value('enabled')
    )->toBe(1);
});

it('keeps protected plugins enabled when disabling all without force', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable --all')->assertSuccessful();

    $this->artisan('tp:plugins disable --all')
        ->expectsOutputToContain('Note: protected plugins remain enabled unless you pass --force.')
        ->assertSuccessful();

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/users')->value('enabled')
    )->toBe(1);

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/admin-shell')->value('enabled')
    )->toBe(1);

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/pages')->value('enabled')
    )->toBe(0);
});

it('disables protected plugins when disabling all with force', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable --all')->assertSuccessful();

    $this->artisan('tp:plugins disable --all --force')
        ->expectsOutputToContain('(forced)')
        ->assertSuccessful();

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/users')->value('enabled')
    )->toBe(0);

    expect(
        DB::table('tp_plugins')->where('id', 'tentapress/admin-shell')->value('enabled')
    )->toBe(0);
});

it('writes and clears plugin cache from command actions', function (): void {
    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable tentapress/pages')->assertSuccessful();

    $cachePath = Paths::pluginCachePath();

    if (is_file($cachePath)) {
        unlink($cachePath);
    }

    $this->artisan('tp:plugins cache')
        ->expectsOutputToContain('Plugin cache rebuilt.')
        ->assertSuccessful();

    expect(is_file($cachePath))->toBeTrue();

    $this->artisan('tp:plugins clear-cache')
        ->expectsOutputToContain('Plugin cache cleared.')
        ->assertSuccessful();

    expect(is_file($cachePath))->toBeFalse();
});

it('fails for unknown plugin command actions', function (): void {
    $this->artisan('tp:plugins nope')
        ->expectsOutputToContain("Unknown action 'nope'.")
        ->assertFailed();
});
