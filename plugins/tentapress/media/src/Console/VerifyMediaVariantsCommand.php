<?php

declare(strict_types=1);

namespace TentaPress\Media\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Models\TpMedia;

final class VerifyMediaVariantsCommand extends Command
{
    protected $signature = 'tp:media:verify-variants {--limit=500 : Max media records to inspect}';

    protected $description = 'Verify original media paths and generated local variant files exist.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $checked = 0;
        $missingOriginal = 0;
        $missingVariants = 0;

        /** @var list<TpMedia> $items */
        $items = TpMedia::query()
            ->whereNotNull('mime_type')
            ->whereLike('mime_type', 'image/%')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->all();

        foreach ($items as $item) {
            $checked++;

            $disk = (string) ($item->disk ?? 'public');
            $storage = Storage::disk($disk);
            $path = trim((string) ($item->path ?? ''));

            if ($path === '' || ! $storage->exists($path)) {
                $missingOriginal++;
                $this->warn("[{$item->id}] Missing original: {$path}");
            }

            $variants = $item->variants;
            if (! is_array($variants)) {
                continue;
            }

            foreach ($variants as $key => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $variantPath = isset($variant['path']) && is_string($variant['path'])
                    ? trim($variant['path'])
                    : '';

                if ($variantPath === '' || ! $storage->exists($variantPath)) {
                    $missingVariants++;
                    $this->warn("[{$item->id}] Missing variant '{$key}': {$variantPath}");
                }
            }
        }

        $this->info("Checked {$checked} image media item(s). Missing originals: {$missingOriginal}. Missing variants: {$missingVariants}.");

        return ($missingOriginal + $missingVariants) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
