<?php

declare(strict_types=1);

namespace TentaPress\Media\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Support\MediaVariantMaintenance;
use Throwable;

final class BackfillMediaVariantsCommand extends Command
{
    protected $signature = 'tp:media:backfill-variants {--limit=200 : Max media records to process}';

    protected $description = 'Backfill local image variants for existing media that are missing them.';

    public function __construct(private readonly MediaVariantMaintenance $maintenance)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $processed = 0;
        $failed = 0;

        /** @var list<TpMedia> $items */
        $items = TpMedia::query()
            ->whereNotNull('mime_type')
            ->whereLike('mime_type', 'image/%')
            ->where(static function (Builder $builder): void {
                $builder
                    ->whereNull('variants')
                    ->orWhereNull('preview_variant');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->all();

        foreach ($items as $item) {
            try {
                $this->maintenance->refresh($item);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $this->warn("[{$item->id}] {$e->getMessage()}");
            }
        }

        $this->info("Backfilled {$processed} media item(s). Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
