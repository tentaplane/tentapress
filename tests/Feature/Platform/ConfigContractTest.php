<?php

declare(strict_types=1);

it('uses the expected test environment contracts', function (): void {
    expect(config('app.env'))->toBe('testing');
    expect(config('database.default'))->toBe('sqlite');
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
    expect(config('session.driver'))->toBe('array');
    expect(config('cache.default'))->toBe('array');
    expect(config('queue.default'))->toBe('sync');
});
