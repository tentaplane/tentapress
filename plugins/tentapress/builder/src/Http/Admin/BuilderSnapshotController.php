<?php

declare(strict_types=1);

namespace TentaPress\Builder\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use TentaPress\Builder\Support\PreviewSnapshotStore;
use TentaPress\Pages\Support\BlocksNormalizer as PageBlocksNormalizer;
use TentaPress\Posts\Support\BlocksNormalizer as PostBlocksNormalizer;

final readonly class BuilderSnapshotController
{
    public function __construct(
        private PageBlocksNormalizer $pageNormalizer,
        private PostBlocksNormalizer $postNormalizer,
        private PreviewSnapshotStore $snapshots,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resource' => ['required', Rule::in(['pages', 'posts'])],
            'layout' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'blocks' => ['present', 'array'],
        ]);

        $resource = (string) $data['resource'];
        $this->authorizeForResource($resource);

        $rawBlocks = is_array($data['blocks'] ?? null) ? $data['blocks'] : [];

        $blocks = $resource === 'pages'
            ? $this->pageNormalizer->normalize($rawBlocks)
            : $this->postNormalizer->normalize($rawBlocks);

        $userId = (int) (Auth::user()?->id ?? 0);

        $token = $this->snapshots->put($userId, [
            'resource' => $resource,
            'layout' => (string) ($data['layout'] ?? 'default'),
            'title' => (string) ($data['title'] ?? 'Preview'),
            'slug' => (string) ($data['slug'] ?? ''),
            'blocks' => $blocks,
        ]);

        return response()->json([
            'token' => $token,
            'preview_url' => route('tp.builder.preview.show', ['token' => $token]),
        ]);
    }

    private function authorizeForResource(string $resource): void
    {
        $user = Auth::user();
        $capability = $resource === 'pages' ? 'manage_pages' : 'manage_posts';

        abort_unless(is_object($user) && method_exists($user, 'can') && $user->can($capability), 403);
    }
}
