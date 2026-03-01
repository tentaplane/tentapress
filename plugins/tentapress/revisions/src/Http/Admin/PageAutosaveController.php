<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Revisions\Services\RevisionRecorder;

final readonly class PageAutosaveController
{
    public function __construct(
        private RevisionRecorder $recorder,
    ) {
    }

    public function __invoke(Request $request, TpPage $page): JsonResponse
    {
        $revision = $this->recorder->capturePageFromRequest($page, $request, 'autosave');

        return response()->json([
            'saved' => $revision !== null,
            'revision_id' => $revision?->id,
            'revision_kind' => 'autosave',
            'saved_at' => $revision?->created_at?->toAtomString(),
        ]);
    }
}
