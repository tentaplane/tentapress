<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionHistory;

final readonly class PostCompareController
{
    public function __construct(
        private RevisionHistory $history,
    ) {
    }

    public function __invoke(Request $request, TpPost $post)
    {
        $revisions = $this->history->revisionsFor('posts', (int) $post->id, 20)->values();
        abort_if($revisions->count() < 2, 404, 'Not enough revisions to compare.');

        $right = $this->resolveRevision($revisions, (int) $request->integer('right'));
        $left = $this->resolveRevision($revisions, (int) $request->integer('left'), 1);

        if ($left->id === $right->id) {
            $left = $revisions->firstWhere('id', '!=', $right->id) ?? $left;
        }

        return view('tentapress-revisions::compare', [
            'resourceType' => 'posts',
            'resource' => $post,
            'resourceLabel' => 'Post',
            'backUrl' => route('tp.posts.edit', ['post' => $post->id]),
            'leftRevision' => $left,
            'rightRevision' => $right,
            'changes' => $this->history->compare($left, $right),
        ]);
    }

    private function resolveRevision($revisions, int $requestedId, int $fallbackIndex = 0): TpRevision
    {
        $revision = $requestedId > 0 ? $revisions->firstWhere('id', $requestedId) : null;

        return $revision instanceof TpRevision ? $revision : $revisions->get($fallbackIndex);
    }
}
