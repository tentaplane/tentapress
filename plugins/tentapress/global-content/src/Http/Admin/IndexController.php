<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\GlobalContent\Models\TpGlobalContent;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $kind = trim((string) $request->query('kind', ''));
        $status = trim((string) $request->query('status', ''));

        $contents = TpGlobalContent::query()
            ->withCount('usages')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->whereLike('title', '%'.$search.'%')
                        ->orWhereLike('slug', '%'.$search.'%')
                        ->orWhereLike('description', '%'.$search.'%');
                });
            })
            ->when(in_array($kind, ['section', 'template_part'], true), fn ($query) => $query->where('kind', $kind))
            ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('tentapress-global-content::global-content.index', [
            'contents' => $contents,
            'search' => $search,
            'kind' => $kind,
            'status' => $status,
        ]);
    }
}
