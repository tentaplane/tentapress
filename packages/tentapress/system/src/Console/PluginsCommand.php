<?php

declare(strict_types=1);

namespace TentaPress\System\Console;

use Illuminate\Console\Command;
use TentaPress\System\Plugin\PluginRegistry;
use Throwable;

final class PluginsCommand extends Command
{
    protected $signature = 'tp:plugins
        {action : sync|list|enable|disable|defaults|cache|clear-cache}
        {id? : plugin id vendor/name}
        {--all : apply to all plugins}
        {--force : force dangerous actions (e.g. disable protected plugins)}
    ';

    protected $description = 'Manage TentaPress plugins (sync/list/enable/disable/defaults/cache)';

    public function __construct(private readonly PluginRegistry $registry)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $id = $this->argument('id');
        $all = (bool) $this->option('all');
        $force = (bool) $this->option('force');

        try {
            $result = match ($action) {
                'sync' => $this->doSync(),
                'list' => $this->doList(),
                'enable' => $this->doEnable($id, $all),
                'disable' => $this->doDisable($id, $all, $force),
                'defaults' => $this->doDefaults(),
                'cache' => $this->doCache(),
                'clear-cache' => $this->doClearCache(),
                default => $this->fail("Unknown action '{$action}'. Expected: sync|list|enable|disable|defaults|cache|clear-cache"),
            };

            return $result;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function doSync(): int
    {
        $count = $this->registry->sync();
        $this->info("Synced {$count} plugin(s).");

        $this->registry->writeCache();
        $this->info('Plugin cache rebuilt.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function doDefaults(): int
    {
        $result = $this->registry->enableDefaults();
        $enabled = (int) ($result['enabled'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);
        $skippedIds = $result['skipped_ids'] ?? [];

        $this->info("Enabled {$enabled} default plugin(s).");

        if ($skipped > 0 && is_array($skippedIds)) {
            $this->warn('Skipped defaults that are not installed: '.implode(', ', array_map(strval(...), $skippedIds)));
            $this->line('Install one with: composer require vendor/name');
        }

        $this->registry->writeCache();
        $this->info('Plugin cache rebuilt.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function doList(): int
    {
        $rows = $this->registry->listAll();

        if ($rows === []) {
            $this->line('No plugins found. Run: php artisan tp:plugins sync');

            return self::SUCCESS;
        }

        $table = array_map(static fn (array $r): array => [
            'id' => (string) ($r['id'] ?? ''),
            'enabled' => ((int) ($r['enabled'] ?? 0)) === 1 ? 'yes' : 'no',
            'version' => (string) ($r['version'] ?? ''),
            'provider' => (string) ($r['provider'] ?? ''),
            'path' => (string) ($r['path'] ?? ''),
        ], $rows);

        $this->table(['id', 'enabled', 'version', 'provider', 'path'], $table);

        return self::SUCCESS;
    }

    private function doEnable(mixed $id, bool $all): int
    {
        if ($all) {
            $count = $this->registry->enableAll();
            $this->info("Enabled {$count} plugin(s).");
        } else {
            if (! is_string($id) || $id === '') {
                return $this->fail('Missing plugin id. Use: php artisan tp:plugins enable vendor/name OR add --all');
            }

            $this->registry->enable($id);
            $this->info("Enabled {$id}.");
        }

        $this->registry->writeCache();
        $this->info('Plugin cache rebuilt.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function doDisable(mixed $id, bool $all, bool $force): int
    {
        if ($all) {
            $count = $this->registry->disableAll(force: $force);
            $this->info("Disabled {$count} plugin(s).".($force ? ' (forced)' : ''));

            if (! $force) {
                $this->line('Note: protected plugins remain enabled unless you pass --force.');
            }
        } else {
            if (! is_string($id) || $id === '') {
                return $this->fail('Missing plugin id. Use: php artisan tp:plugins disable vendor/name OR add --all');
            }

            $this->registry->disable($id, force: $force);
            $this->info("Disabled {$id}.".($force ? ' (forced)' : ''));
        }

        $this->registry->writeCache();
        $this->info('Plugin cache rebuilt.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function doCache(): int
    {
        $this->registry->writeCache();
        $this->info('Plugin cache rebuilt.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function doClearCache(): int
    {
        $this->registry->clearCache();
        $this->info('Plugin cache cleared.');
        $this->clearViewCache();

        return self::SUCCESS;
    }

    private function clearViewCache(): void
    {
        $this->callSilent('view:clear');
        $this->info('View cache cleared.');
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
