<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionHistory;
use TentaPress\Revisions\Services\RevisionRecorder;

final readonly class PageRestoreController
{
    public function __construct(
        private RevisionHistory $history,
        private RevisionRecorder $recorder,
    ) {
    }

    public function __invoke(TpPage $page, TpRevision $revision)
    {
        abort_unless($revision->resource_type === 'pages' && (int) $revision->resource_id === (int) $page->id, 404);

        $page = $this->history->restorePage($page, $revision);
        $page->updated_by = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;
        $page->save();

        $this->recorder->capturePage($page, 'restore', (int) $revision->id);

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Revision restored.');
    }
}
