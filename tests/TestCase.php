<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $token = trim((string) getenv('TEST_TOKEN'));

        if ($token === '') {
            return;
        }

        $compiledViewsPath = storage_path('framework/views-'.$token);

        if (! is_dir($compiledViewsPath)) {
            mkdir($compiledViewsPath, 0755, true);
        }

        config()->set('view.compiled', $compiledViewsPath);
    }
}
