<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin;

use Illuminate\Http\RedirectResponse;
use TentaPress\ContentTypes\Models\TpContentType;

final class DestroyController
{
    public function __invoke(TpContentType $contentType): RedirectResponse
    {
        $contentType->delete();

        return to_route('tp.content-types.index')
            ->with('tp_notice_success', 'Content type deleted.');
    }
}
