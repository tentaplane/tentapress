<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use TentaPress\Pages\Models\TpPage;

final class DestroyController
{
    public function __invoke(TpPage $page)
    {
        $page->delete();

        return to_route('tp.pages.index')
            ->with('tp_notice_success', 'Page deleted.');
    }
}
