<?php

declare(strict_types=1);

namespace TentaPress\Users\Console;

use Illuminate\Console\Command;
use TentaPress\Users\Database\SeedPermissions;
use Throwable;

final class PermissionsCommand extends Command
{
    protected $signature = 'tp:permissions {action : seed}';

    protected $description = 'Permissions utilities (seed roles/capabilities)';

    public function __construct(
        private readonly SeedPermissions $seeder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');

        try {
            return match ($action) {
                'seed' => $this->seed(),
                default => $this->fail("Unknown action '{$action}'. Expected: seed"),
            };
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function seed(): int
    {
        $this->seeder->run();
        $this->info('Seeded roles and capabilities.');

        return self::SUCCESS;
    }

    // Must be compatible with Illuminate\Console\Command::fail(Throwable|string|null $exception = null)
    public function fail(\Throwable|string|null $exception = null): int
    {
        $message = is_string($exception) ? $exception : ($exception?->getMessage() ?? 'Command failed.');
        $this->error($message);

        return self::FAILURE;
    }
}
