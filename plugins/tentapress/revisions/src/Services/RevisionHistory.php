<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;

final class RevisionHistory
{
    /**
     * @return Collection<int,TpRevision>
     */
    public function revisionsFor(string $resourceType, int $resourceId, int $limit = 20): Collection
    {
        return TpRevision::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function latestAutosaveFor(string $resourceType, int $resourceId, ?CarbonInterface $updatedAt): ?TpRevision
    {
        $revision = TpRevision::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('revision_kind', 'autosave')
            ->latest('id')
            ->first();

        if (! $revision instanceof TpRevision) {
            return null;
        }

        if ($updatedAt instanceof CarbonInterface && $revision->created_at !== null && $revision->created_at->lt($updatedAt)) {
            return null;
        }

        return $revision;
    }

    /**
     * @return array<int,array{field:string,label:string,left:string,right:string}>
     */
    public function compare(TpRevision $left, TpRevision $right): array
    {
        $fields = [
            'title' => 'Title',
            'slug' => 'Slug',
            'status' => 'Status',
            'layout' => 'Layout',
            'editor_driver' => 'Editor driver',
            'author_id' => 'Author ID',
            'published_at' => 'Published at',
            'blocks' => 'Blocks JSON',
            'content' => 'Document JSON',
        ];

        $changes = [];

        foreach ($fields as $field => $label) {
            $leftValue = $this->normalizeField($left, $field);
            $rightValue = $this->normalizeField($right, $field);

            if ($leftValue === $rightValue) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $label,
                'left' => $leftValue,
                'right' => $rightValue,
            ];
        }

        return $changes;
    }

    public function restorePage(TpPage $page, TpRevision $revision): TpPage
    {
        $payload = [
            'title' => (string) $revision->title,
            'slug' => (string) $revision->slug,
            'status' => (string) $revision->status,
            'layout' => $revision->layout !== null ? (string) $revision->layout : null,
            'blocks' => is_array($revision->blocks) ? $revision->blocks : [],
            'published_at' => $revision->published_at,
        ];

        if (Schema::hasColumn('tp_pages', 'editor_driver')) {
            $payload['editor_driver'] = (string) $revision->editor_driver;
        }
        if (Schema::hasColumn('tp_pages', 'content')) {
            $payload['content'] = is_array($revision->content) ? $revision->content : null;
        }

        $page->fill($payload);

        $page->save();

        return $page;
    }

    public function restorePost(TpPost $post, TpRevision $revision): TpPost
    {
        $payload = [
            'title' => (string) $revision->title,
            'slug' => (string) $revision->slug,
            'status' => (string) $revision->status,
            'layout' => $revision->layout !== null ? (string) $revision->layout : null,
            'blocks' => is_array($revision->blocks) ? $revision->blocks : [],
            'author_id' => $revision->author_id !== null ? (int) $revision->author_id : null,
            'published_at' => $revision->published_at,
        ];

        if (Schema::hasColumn('tp_posts', 'editor_driver')) {
            $payload['editor_driver'] = (string) $revision->editor_driver;
        }
        if (Schema::hasColumn('tp_posts', 'content')) {
            $payload['content'] = is_array($revision->content) ? $revision->content : null;
        }

        $post->fill($payload);

        $post->save();

        return $post;
    }

    private function normalizeField(TpRevision $revision, string $field): string
    {
        $value = $revision->getAttribute($field);

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? '[]' : $encoded;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
