<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionHistory;
use TentaPress\Revisions\Services\RevisionRecorder;

final readonly class PostRestoreController
{
    public function __construct(
        private RevisionHistory $history,
        private RevisionRecorder $recorder,
    ) {
    }

    public function __invoke(TpPost $post, TpRevision $revision)
    {
        abort_unless($revision->resource_type === 'posts' && (int) $revision->resource_id === (int) $post->id, 404);

        $post = $this->history->restorePost($post, $revision);
        $post->updated_by = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;
        $post->save();

        $this->recorder->capturePost($post, 'restore', (int) $revision->id);

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Revision restored.');
    }
}
