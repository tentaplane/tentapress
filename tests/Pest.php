<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->in(
    __DIR__.'/Feature',
    __DIR__.'/../plugins',
    __DIR__.'/../packages',
);

pest()->use(RefreshDatabase::class)->in(
    __DIR__.'/../plugins',
    __DIR__.'/../packages',
);
