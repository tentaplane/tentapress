<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;
use TentaPress\SystemInfo\Models\TpPluginInstall;
use TentaPress\SystemInfo\Support\CommandBinaryResolver;

final class InstallPlugin implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;

    public function __construct(
        public readonly int $installId,
    ) {
    }

    /**
     * @return array<int,mixed>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('tp_plugin_lifecycle_jobs'))->expireAfter($this->timeout + 60),
        ];
    }

    public function handle(CommandBinaryResolver $binaryResolver): void
    {
        $install = TpPluginInstall::query()->find($this->installId);
        if (! $install instanceof TpPluginInstall) {
            return;
        }

        $package = trim((string) $install->package);
        if ($package === '') {
            $this->markFailed($install, 'Missing package name.', '');

            return;
        }

        $install->status = 'running';
        $install->started_at = now();
        $install->error = null;
        $install->save();

        $log = '';
        $phpBinary = $binaryResolver->phpCliBinary();

        try {
            $this->runCommand($this->composerRequireCommand($package, $phpBinary, $binaryResolver), $log);
            $this->runCommand([$phpBinary, 'artisan', 'tp:plugins', 'sync', '--no-interaction'], $log);
            $this->runCommand([$phpBinary, 'artisan', 'tp:plugins', 'enable', $package, '--no-interaction'], $log);
            $this->runCommand([$phpBinary, 'artisan', 'migrate', '--force', '--no-interaction'], $log);

            $install->status = 'success';
            $install->output = $this->truncateLog($log);
            $install->error = null;
            $install->finished_at = now();
            $install->save();
        } catch (Throwable $e) {
            $this->markFailed($install, $e->getMessage(), $log);
        }
    }

    private function runCommand(array $command, string &$log): void
    {
        $log .= '$ ' . implode(' ', $command) . "\n";

        $environment = $this->buildComposerEnvironment();

        $result = Process::path(base_path())
            ->env($environment)
            ->timeout($this->timeout)
            ->run($command);

        $output = trim((string) $result->output() . (string) $result->errorOutput());
        if ($output !== '') {
            $log .= $output . "\n\n";
        }

        if (! $result->successful()) {
            throw new RuntimeException($this->buildCommandError($command, $output));
        }
    }

    /**
     * @return array<int,string>
     */
    private function composerRequireCommand(string $package, string $phpBinary, CommandBinaryResolver $binaryResolver): array
    {
        return [...$binaryResolver->composerBaseCommand($phpBinary), 'require', $package, '--no-interaction', '--no-progress'];
    }

    /**
     * @return array<string,string>
     */
    private function buildComposerEnvironment(): array
    {
        $composerLocal = base_path('composer.local.json');
        if (! is_file($composerLocal)) {
            return [];
        }

        return [
            'COMPOSER' => $composerLocal,
        ];
    }

    /**
     * @param  array<int,string>  $command
     */
    private function buildCommandError(array $command, string $output): string
    {
        $prefix = 'Command failed: ' . implode(' ', $command);
        if ($output === '') {
            return $prefix;
        }

        $tail = mb_substr($output, -500);

        return $prefix . ' | ' . $tail;
    }

    private function markFailed(TpPluginInstall $install, string $message, string $log): void
    {
        $install->status = 'failed';
        $install->error = $message;
        $install->output = $this->truncateLog($log);
        $install->finished_at = now();
        $install->save();
    }

    private function truncateLog(string $log): string
    {
        $limit = 120000;

        if (strlen($log) <= $limit) {
            return $log;
        }

        return substr($log, 0, $limit) . "\n\n[output truncated]";
    }
}
