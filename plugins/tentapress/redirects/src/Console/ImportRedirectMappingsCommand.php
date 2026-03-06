<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use TentaPress\Redirects\Models\TpRedirect;
use TentaPress\Redirects\Services\RedirectManager;
use TentaPress\Redirects\Services\RedirectSuggestionManager;

final class ImportRedirectMappingsCommand extends Command
{
    protected $signature = 'tp:redirects:import-mappings {path : Mapping report path (absolute or storage/app relative)} {--status=301 : Redirect status code (301 or 302)}';

    protected $description = 'Import redirect mappings from a TentaPress import URL mapping report.';

    public function handle(RedirectManager $manager, RedirectSuggestionManager $suggestions): int
    {
        $inputPath = trim((string) $this->argument('path'));
        $statusCode = (int) $this->option('status');

        $resolvedPath = $this->resolvePath($inputPath);

        if (! File::exists($resolvedPath)) {
            $this->components->error("Mapping report not found: {$resolvedPath}");

            return self::FAILURE;
        }

        $decoded = json_decode((string) File::get($resolvedPath), true);
        if (! is_array($decoded) || ! is_array($decoded['mappings'] ?? null)) {
            $this->components->error('Invalid mapping report format. Expected top-level "mappings" array.');

            return self::FAILURE;
        }

        $created = 0;
        $staged = 0;
        $skipped = 0;

        foreach ($decoded['mappings'] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $sourceUrl = trim((string) ($row['source_url'] ?? ''));
            $destinationUrl = trim((string) ($row['destination_url'] ?? ''));

            if ($sourceUrl === '' || $destinationUrl === '') {
                $skipped++;

                continue;
            }

            $sourcePath = (string) (parse_url($sourceUrl, PHP_URL_PATH) ?? '');
            $targetPath = (string) (parse_url($destinationUrl, PHP_URL_PATH) ?? '');
            if ($sourcePath === '' || $targetPath === '') {
                $skipped++;

                continue;
            }

            $alreadyExists = TpRedirect::query()->fromSource('/'.ltrim($sourcePath, '/'))->exists();
            if ($alreadyExists) {
                $suggestions->stage($sourcePath, $targetPath, $statusCode, 'import', [
                    'conflict_type' => 'existing_source',
                    'source_url' => $sourceUrl,
                    'destination_url' => $destinationUrl,
                ], 'existing_source');
                $staged++;

                continue;
            }

            try {
                $manager->create([
                    'source_path' => $sourcePath,
                    'target_path' => $targetPath,
                    'status_code' => $statusCode,
                    'is_enabled' => true,
                    'origin' => 'import',
                    'notes' => 'Imported from mapping report.',
                ]);
                $created++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        $this->components->info("Imported redirects: {$created}; staged suggestions: {$staged}; skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        $storagePath = storage_path('app/'.ltrim($path, '/'));
        if (File::exists($storagePath)) {
            return $storagePath;
        }

        return base_path($path);
    }
}
