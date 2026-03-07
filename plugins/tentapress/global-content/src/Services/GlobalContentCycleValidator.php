<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use TentaPress\GlobalContent\Models\TpGlobalContent;

final readonly class GlobalContentCycleValidator
{
    public function __construct(
        private GlobalContentReferenceExtractor $extractor,
    ) {
    }

    /**
     * @param  array<int,mixed>  $blocks
     */
    public function assertValid(array $blocks, ?int $currentId = null): void
    {
        $referenceIds = $this->extractor->fromBlocks($blocks);
        if ($referenceIds === []) {
            return;
        }

        $records = TpGlobalContent::query()
            ->whereIn('id', $referenceIds)
            ->get(['id', 'title', 'blocks']);

        $recordsById = $records->keyBy('id');
        $missingIds = array_values(array_diff($referenceIds, $recordsById->keys()->all()));

        if ($missingIds !== []) {
            throw ValidationException::withMessages([
                'blocks_json' => 'Referenced global content entries were not found: '.implode(', ', $missingIds).'.',
            ]);
        }

        if ($currentId !== null && $this->introducesCycle($currentId, $referenceIds, $recordsById)) {
            throw ValidationException::withMessages([
                'blocks_json' => 'This content creates a recursive global content reference chain.',
            ]);
        }
    }

    /**
     * @param  array<int,int>  $referenceIds
     * @param  Collection<int,TpGlobalContent>  $recordsById
     */
    private function introducesCycle(int $currentId, array $referenceIds, Collection $recordsById): bool
    {
        foreach ($referenceIds as $referenceId) {
            if ($referenceId === $currentId) {
                return true;
            }

            if ($this->containsTarget($referenceId, $currentId, [], $recordsById)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int,int>  $trail
     * @param  Collection<int,TpGlobalContent>  $recordsById
     */
    private function containsTarget(int $referenceId, int $targetId, array $trail, Collection $recordsById): bool
    {
        if (in_array($referenceId, $trail, true)) {
            return false;
        }

        if ($referenceId === $targetId) {
            return true;
        }

        /** @var TpGlobalContent|null $record */
        $record = $recordsById->get($referenceId);

        if (! $record instanceof TpGlobalContent) {
            $record = TpGlobalContent::query()->find($referenceId, ['id', 'blocks']);
            if (! $record instanceof TpGlobalContent) {
                return false;
            }
        }

        $nestedIds = $this->extractor->fromBlocks(is_array($record->blocks) ? $record->blocks : []);
        $nextTrail = [...$trail, $referenceId];

        foreach ($nestedIds as $nestedId) {
            if ($this->containsTarget($nestedId, $targetId, $nextTrail, $recordsById)) {
                return true;
            }
        }

        return false;
    }
}
