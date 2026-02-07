<?php

declare(strict_types=1);

namespace TentaPress\Media\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Support\MediaVariantMaintenance;
use Throwable;

final class OptimizeMediaCommand extends Command
{
    protected $signature = 'tp:media:optimise {--limit=200 : Max media records to process} {--force : Rebuild variants for all image media}';

    protected $description = 'Generate or refresh local variants for image media.';

    public function __construct(private readonly MediaVariantMaintenance $maintenance)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $force = (bool) $this->option('force');

        $processed = 0;
        $failed = 0;

        $query = TpMedia::query()
            ->whereNotNull('mime_type')
            ->whereLike('mime_type', 'image/%')
            ->orderBy('id');

        if (! $force) {
            $query->where(static function (Builder $builder): void {
                $builder
                    ->whereNull('optimization_status')
                    ->orWhereIn('optimization_status', ['pending', 'failed'])
                    ->orWhereNull('variants');
            });
        }

        /** @var list<TpMedia> $items */
        $items = $query->limit($limit)->get()->all();

        foreach ($items as $item) {
            try {
                $this->maintenance->refresh($item);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                $this->warn("[{$item->id}] {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processed} media item(s). Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
