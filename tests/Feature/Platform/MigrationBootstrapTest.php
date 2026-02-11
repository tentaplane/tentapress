<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('has migrated core and plugin tables available after plugin orchestration', function (): void {
    expect(Schema::hasTable('tp_plugins'))->toBeTrue();
    expect(Schema::hasTable('tp_users'))->toBeTrue();
    expect(Schema::hasTable('tp_pages'))->toBeTrue();

    $this->artisan('tp:plugins sync')->assertSuccessful();
    $this->artisan('tp:plugins enable --all')->assertSuccessful();
    $this->artisan('migrate --force')->assertSuccessful();
});
