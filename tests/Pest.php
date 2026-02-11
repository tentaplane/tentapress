<?php

declare(strict_types=1);

use Tests\TestCase;

pest()->extend(TestCase::class)->in(
    __DIR__.'/Feature',
    __DIR__.'/../plugins',
    __DIR__.'/../packages',
);
