<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Services\RevisionRecorder;

final readonly class PostAutosaveController
{
    public function __construct(
        private RevisionRecorder $recorder,
    ) {
    }

    public function __invoke(Request $request, TpPost $post): JsonResponse
    {
        $revision = $this->recorder->capturePostFromRequest($post, $request, 'autosave');

        return response()->json([
            'saved' => $revision !== null,
            'revision_id' => $revision?->id,
            'revision_kind' => 'autosave',
            'saved_at' => $revision?->created_at?->toAtomString(),
        ]);
    }
}
