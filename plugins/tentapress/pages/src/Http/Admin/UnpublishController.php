<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Pages\Models\TpPage;

final class UnpublishController
{
    public function __invoke(TpPage $page)
    {
        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $page->status = 'draft';
        $page->updated_by = $nowUserId ?: null;
        $page->save();

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page set to draft.');
    }
}
