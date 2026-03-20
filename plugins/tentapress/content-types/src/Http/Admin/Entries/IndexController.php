<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentType;

final class IndexController
{
    public function __invoke(TpContentType $contentType): View
    {
        $entries = $contentType->entries()
            ->latest('updated_at')
            ->paginate(15);

        return view('tentapress-content-types::entries.index', [
            'contentType' => $contentType,
            'entries' => $entries,
        ]);
    }
}
