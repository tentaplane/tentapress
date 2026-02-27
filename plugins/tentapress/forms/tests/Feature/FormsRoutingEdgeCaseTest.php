<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;

it('applies named throttle middleware to form submissions', function (): void {
    $route = app('router')->getRoutes()->getByName('tp.forms.submit');

    expect($route)->not->toBeNull();
    expect($route?->gatherMiddleware())->toContain('throttle:tp.forms.submit');
    expect(RateLimiter::limiter('tp.forms.submit'))->not->toBeNull();
});
