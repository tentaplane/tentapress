<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Services;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;

final class RevisionRecorder
{
    public function capturePage(TpPage $page): ?TpRevision
    {
        return $this->capture(
            resourceType: 'pages',
            resourceId: (int) $page->id,
            payload: [
                'title' => (string) $page->title,
                'slug' => (string) $page->slug,
                'status' => (string) $page->status,
                'layout' => $page->layout !== null ? (string) $page->layout : null,
                'editor_driver' => (string) $page->editor_driver,
                'blocks' => is_array($page->blocks) ? $page->blocks : [],
                'content' => is_array($page->content) ? $page->content : null,
                'author_id' => null,
                'published_at' => $page->published_at?->toAtomString(),
                'created_by' => $this->normalizeInteger($page->updated_by ?? $page->created_by ?? null),
            ],
        );
    }

    public function capturePost(TpPost $post): ?TpRevision
    {
        return $this->capture(
            resourceType: 'posts',
            resourceId: (int) $post->id,
            payload: [
                'title' => (string) $post->title,
                'slug' => (string) $post->slug,
                'status' => (string) $post->status,
                'layout' => $post->layout !== null ? (string) $post->layout : null,
                'editor_driver' => (string) $post->editor_driver,
                'blocks' => is_array($post->blocks) ? $post->blocks : [],
                'content' => is_array($post->content) ? $post->content : null,
                'author_id' => $this->normalizeInteger($post->author_id ?? null),
                'published_at' => $post->published_at?->toAtomString(),
                'created_by' => $this->normalizeInteger($post->updated_by ?? $post->created_by ?? null),
            ],
        );
    }

    /**
     * @param array{
     *     title:string,
     *     slug:string,
     *     status:string,
     *     layout:?string,
     *     editor_driver:string,
     *     blocks:array<int|string,mixed>,
     *     content:array<int|string,mixed>|null,
     *     author_id:?int,
     *     published_at:?string,
     *     created_by:?int
     * } $payload
     */
    private function capture(string $resourceType, int $resourceId, array $payload): ?TpRevision
    {
        if ($resourceId <= 0) {
            return null;
        }

        $snapshotHash = sha1((string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $latest = TpRevision::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->latest('id')
            ->first();

        if ($latest instanceof TpRevision && (string) $latest->snapshot_hash === $snapshotHash) {
            return null;
        }

        return TpRevision::query()->create([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'title' => $payload['title'],
            'slug' => $payload['slug'],
            'status' => $payload['status'],
            'layout' => $payload['layout'],
            'editor_driver' => $payload['editor_driver'],
            'blocks' => $payload['blocks'],
            'content' => $payload['content'],
            'author_id' => $payload['author_id'],
            'published_at' => $payload['published_at'],
            'created_by' => $payload['created_by'],
            'snapshot_hash' => $snapshotHash,
        ]);
    }

    private function normalizeInteger(mixed $value): ?int
    {
        $normalized = (int) $value;

        return $normalized > 0 ? $normalized : null;
    }
}
