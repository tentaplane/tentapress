<?php

declare(strict_types=1);

namespace TentaPress\System\Console;

use Illuminate\Console\Command;
use TentaPress\System\Theme\ThemeManager;
use TentaPress\System\Theme\ThemeRegistry;
use Throwable;

final class ThemesCommand extends Command
{
    protected $signature = 'tp:themes
        {action : sync|list|activate|cache|clear-cache}
        {id? : theme id vendor/name (required for activate)}
    ';

    protected $description = 'Manage TentaPress themes (sync/list/activate/cache)';

    public function __construct(
        private readonly ThemeRegistry $registry,
        private readonly ThemeManager $manager,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $id = $this->argument('id');

        try {
            return match ($action) {
                'sync' => $this->doSync(),
                'list' => $this->doList(),
                'activate' => $this->doActivate($id),
                'cache' => $this->doCache(),
                'clear-cache' => $this->doClearCache(),
                default => $this->fail("Unknown action '{$action}'. Expected: sync|list|activate|cache|clear-cache"),
            };
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function doSync(): int
    {
        $count = $this->registry->sync();
        $this->info("Synced {$count} theme(s).");

        return self::SUCCESS;
    }

    private function doList(): int
    {
        $rows = $this->registry->listAll();

        $active = $this->manager->activeTheme();
        $activeId = $active['id'] ?? null;

        if ($rows === []) {
            $this->line('No themes found. Run: php artisan tp:themes sync');

            return self::SUCCESS;
        }

        $table = array_map(static function (array $r) use ($activeId): array {
            $id = (string) ($r['id'] ?? '');

            return [
                'id' => $id,
                'active' => ($activeId === $id) ? 'yes' : '',
                'name' => (string) ($r['name'] ?? ''),
                'version' => (string) ($r['version'] ?? ''),
                'path' => (string) ($r['path'] ?? ''),
            ];
        }, $rows);

        $this->table(['id', 'active', 'name', 'version', 'path'], $table);

        return self::SUCCESS;
    }

    private function doActivate(mixed $id): int
    {
        if (! is_string($id) || $id === '') {
            return $this->fail('Missing theme id. Use: php artisan tp:themes activate vendor/name');
        }

        $this->manager->activate($id);

        $this->info("Activated theme {$id}.");
        $this->info('Theme cache rebuilt.');

        return self::SUCCESS;
    }

    private function doCache(): int
    {
        $this->manager->writeCache();
        $this->info('Theme cache rebuilt.');

        return self::SUCCESS;
    }

    private function doClearCache(): int
    {
        $this->manager->clearCache();
        $this->info('Theme cache cleared.');

        return self::SUCCESS;
    }
}
