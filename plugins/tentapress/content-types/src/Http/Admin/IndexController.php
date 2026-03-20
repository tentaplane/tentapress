<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentType;

final class IndexController
{
    public function __invoke(): View
    {
        $contentTypes = TpContentType::query()
            ->withCount(['fields', 'entries'])
            ->orderBy('plural_label')
            ->paginate(15);

        return view('tentapress-content-types::content-types.index', [
            'contentTypes' => $contentTypes,
        ]);
    }
}
