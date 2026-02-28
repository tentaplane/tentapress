<?php

declare(strict_types=1);

namespace TentaPress\System\Console;

use Illuminate\Console\Command;
use TentaPress\System\Catalog\FirstPartyPluginCatalogGenerator;
use Throwable;

final class CatalogCommand extends Command
{
    protected $signature = 'tp:catalog
        {action : generate|check}
        {--path= : Override the catalog file path}
    ';

    protected $description = 'Generate or validate the first-party plugin catalog';

    public function __construct(
        private readonly FirstPartyPluginCatalogGenerator $generator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $path = trim((string) $this->option('path'));
        $path = $path === '' ? null : $path;

        try {
            return match ($action) {
                'generate' => $this->generateCatalog($path),
                'check' => $this->checkCatalog($path),
                default => $this->fail("Unknown action '{$action}'. Expected: generate|check"),
            };
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function generateCatalog(?string $path): int
    {
        $resolvedPath = $this->generator->write($path);
        $payload = $this->generator->generate($resolvedPath);
        $count = count($payload['plugins'] ?? []);

        $this->info("Generated first-party plugin catalog with {$count} plugin(s).");
        $this->line($resolvedPath);

        return self::SUCCESS;
    }

    private function checkCatalog(?string $path): int
    {
        if (! $this->generator->isCurrent($path)) {
            $this->error('First-party plugin catalog is out of date. Run: php artisan tp:catalog generate');

            return self::FAILURE;
        }

        $this->info('First-party plugin catalog is up to date.');

        return self::SUCCESS;
    }

    /**
     * Must match Illuminate\Console\Command::fail(Throwable|string|null $exception = null)
     */
    public function fail(Throwable|string|null $exception = null): int
    {
        if ($exception instanceof Throwable) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (is_string($exception) && $exception !== '') {
            $this->error($exception);

            return self::FAILURE;
        }

        $this->error('Command failed.');

        return self::FAILURE;
    }
}
