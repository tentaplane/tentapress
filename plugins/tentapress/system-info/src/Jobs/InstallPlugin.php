<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Throwable;
use TentaPress\SystemInfo\Models\TpPluginInstall;

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

    public function handle(): void
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

        try {
            $this->runCommand($this->composerRequireCommand($package), $log);
            $this->runCommand([PHP_BINARY, 'artisan', 'tp:plugins', 'sync', '--no-interaction'], $log);
            $this->runCommand([PHP_BINARY, 'artisan', 'tp:plugins', 'enable', $package, '--no-interaction'], $log);

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
    private function composerRequireCommand(string $package): array
    {
        $finder = new ExecutableFinder();
        $projectComposer = base_path('composer.phar');
        if (is_file($projectComposer)) {
            return [PHP_BINARY, $projectComposer, 'require', $package, '--no-interaction', '--no-progress'];
        }

        $herd = $finder->find('herd');
        if (is_string($herd) && $herd !== '') {
            return [$herd, 'composer', 'require', $package, '--no-interaction', '--no-progress'];
        }

        $composer = $finder->find('composer');
        if (is_string($composer) && $composer !== '') {
            return [$composer, 'require', $package, '--no-interaction', '--no-progress'];
        }

        throw new RuntimeException('Composer binary not found for queue worker.');
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
