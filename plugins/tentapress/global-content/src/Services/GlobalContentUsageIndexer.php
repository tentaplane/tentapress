<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Support\Facades\DB;
use TentaPress\GlobalContent\Models\TpGlobalContentUsage;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;

final readonly class GlobalContentUsageIndexer
{
    public function __construct(
        private GlobalContentReferenceExtractor $extractor,
    ) {
    }

    public function reindexPage(TpPage $page): void
    {
        $this->reindex(
            ownerType: 'page',
            ownerId: (int) $page->id,
            ownerLabel: trim((string) $page->title) !== '' ? (string) $page->title : 'Page #'.$page->id,
            editorDriver: (string) $page->editor_driver,
            referenceIds: $this->pageReferenceIds($page),
        );
    }

    public function reindexPost(TpPost $post): void
    {
        $this->reindex(
            ownerType: 'post',
            ownerId: (int) $post->id,
            ownerLabel: trim((string) $post->title) !== '' ? (string) $post->title : 'Post #'.$post->id,
            editorDriver: (string) $post->editor_driver,
            referenceIds: $this->postReferenceIds($post),
        );
    }

    public function forget(string $ownerType, int $ownerId): void
    {
        if ($ownerId <= 0) {
            return;
        }

        TpGlobalContentUsage::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->delete();
    }

    /**
     * @param  array<int,int>  $referenceIds
     */
    private function reindex(
        string $ownerType,
        int $ownerId,
        string $ownerLabel,
        string $editorDriver,
        array $referenceIds,
    ): void {
        if ($ownerId <= 0) {
            return;
        }

        $this->forget($ownerType, $ownerId);

        if ($referenceIds === []) {
            return;
        }

        $rows = array_map(static fn (int $referenceId): array => [
            'global_content_id' => $referenceId,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'owner_label' => $ownerLabel,
            'editor_driver' => $editorDriver !== '' ? $editorDriver : 'blocks',
            'created_at' => now(),
            'updated_at' => now(),
        ], $referenceIds);

        DB::table('tp_global_content_usages')->insert($rows);
    }

    /**
     * @return array<int,int>
     */
    private function pageReferenceIds(TpPage $page): array
    {
        if ((string) $page->editor_driver === 'page') {
            return $this->extractor->fromPageDocument($page->content);
        }

        return $this->extractor->fromBlocks(is_array($page->blocks) ? $page->blocks : []);
    }

    /**
     * @return array<int,int>
     */
    private function postReferenceIds(TpPost $post): array
    {
        if ((string) $post->editor_driver === 'page') {
            return $this->extractor->fromPageDocument($post->content);
        }

        return $this->extractor->fromBlocks(is_array($post->blocks) ? $post->blocks : []);
    }
}
