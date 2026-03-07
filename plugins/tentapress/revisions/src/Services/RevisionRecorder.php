<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Services;

use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;

final class RevisionRecorder
{
    public function capturePage(TpPage $page, string $kind = 'manual', ?int $restoredFromRevisionId = null): ?TpRevision
    {
        return $this->capture(
            resourceType: 'pages',
            resourceId: (int) $page->id,
            kind: $kind,
            restoredFromRevisionId: $restoredFromRevisionId,
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

    public function capturePost(TpPost $post, string $kind = 'manual', ?int $restoredFromRevisionId = null): ?TpRevision
    {
        return $this->capture(
            resourceType: 'posts',
            resourceId: (int) $post->id,
            kind: $kind,
            restoredFromRevisionId: $restoredFromRevisionId,
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

    public function capturePageFromRequest(TpPage $page, Request $request, string $kind = 'autosave'): ?TpRevision
    {
        return $this->capture(
            resourceType: 'pages',
            resourceId: (int) $page->id,
            kind: $kind,
            restoredFromRevisionId: null,
            payload: [
                'title' => (string) $request->input('title', $page->title),
                'slug' => (string) $request->input('slug', $page->slug),
                'status' => (string) $page->status,
                'layout' => $this->normalizeString($request->input('layout', $page->layout)),
                'editor_driver' => $this->normalizeString($request->input('editor_driver', $page->editor_driver)) ?? 'blocks',
                'blocks' => $this->decodeJsonPayload($request->input('blocks_json'), $page->blocks),
                'content' => $this->decodeJsonPayload($request->input('page_doc_json'), $page->content, allowNull: true),
                'author_id' => null,
                'published_at' => $page->published_at?->toAtomString(),
                'created_by' => $this->normalizeInteger($page->updated_by ?? $page->created_by ?? null),
            ],
        );
    }

    public function capturePostFromRequest(TpPost $post, Request $request, string $kind = 'autosave'): ?TpRevision
    {
        return $this->capture(
            resourceType: 'posts',
            resourceId: (int) $post->id,
            kind: $kind,
            restoredFromRevisionId: null,
            payload: [
                'title' => (string) $request->input('title', $post->title),
                'slug' => (string) $request->input('slug', $post->slug),
                'status' => (string) $post->status,
                'layout' => $this->normalizeString($request->input('layout', $post->layout)),
                'editor_driver' => $this->normalizeString($request->input('editor_driver', $post->editor_driver)) ?? 'blocks',
                'blocks' => $this->decodeJsonPayload($request->input('blocks_json'), $post->blocks),
                'content' => $this->decodeJsonPayload($request->input('page_doc_json'), $post->content, allowNull: true),
                'author_id' => $this->normalizeInteger($request->input('author_id', $post->author_id)),
                'published_at' => $this->normalizeString($request->input('published_at')) ?: $post->published_at?->toAtomString(),
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
    private function capture(
        string $resourceType,
        int $resourceId,
        string $kind,
        ?int $restoredFromRevisionId,
        array $payload,
    ): ?TpRevision {
        if ($resourceId <= 0) {
            return null;
        }

        $snapshotHash = sha1((string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $latest = TpRevision::query()
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('revision_kind', $kind)
            ->latest('id')
            ->first();

        if ($latest instanceof TpRevision && (string) $latest->snapshot_hash === $snapshotHash) {
            return null;
        }

        $revision = TpRevision::query()->createOrFirst([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'revision_kind' => $kind,
            'snapshot_hash' => $snapshotHash,
        ], [
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
            'restored_from_revision_id' => $restoredFromRevisionId,
        ]);

        return $revision->wasRecentlyCreated ? $revision : null;
    }

    private function normalizeInteger(mixed $value): ?int
    {
        $normalized = (int) $value;

        return $normalized > 0 ? $normalized : null;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param array<int|string,mixed>|null $fallback
     * @return array<int|string,mixed>|null
     */
    private function decodeJsonPayload(mixed $value, ?array $fallback = [], bool $allowNull = false): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return $allowNull ? $fallback : ($fallback ?? []);
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $allowNull ? $fallback : ($fallback ?? []);
        }

        return $decoded;
    }
}
