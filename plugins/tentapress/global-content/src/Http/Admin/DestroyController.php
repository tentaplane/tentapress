<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use TentaPress\GlobalContent\Models\TpGlobalContent;

final class DestroyController
{
    public function __invoke(TpGlobalContent $globalContent)
    {
        $globalContent->delete();

        return to_route('tp.global-content.index')
            ->with('tp_notice_success', 'Global content deleted.');
    }
}
