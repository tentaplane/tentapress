<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('loads system package migrations during test setup', function (): void {
    expect(Schema::hasTable('tp_plugins'))->toBeTrue();
});
