<?php

declare(strict_types=1);

namespace TentaPress\Revisions\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionHistory;

final readonly class PageCompareController
{
    public function __construct(
        private RevisionHistory $history,
    ) {
    }

    public function __invoke(Request $request, TpPage $page)
    {
        $revisions = $this->history->revisionsFor('pages', (int) $page->id, 20)->values();
        abort_if($revisions->count() < 2, 404, 'Not enough revisions to compare.');

        $right = $this->resolveRevision($revisions, (int) $request->integer('right'));
        $left = $this->resolveRevision($revisions, (int) $request->integer('left'), 1);

        if ($left->id === $right->id) {
            $left = $revisions->firstWhere('id', '!=', $right->id) ?? $left;
        }

        return view('tentapress-revisions::compare', [
            'resourceType' => 'pages',
            'resource' => $page,
            'resourceLabel' => 'Page',
            'backUrl' => route('tp.pages.edit', ['page' => $page->id]),
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
