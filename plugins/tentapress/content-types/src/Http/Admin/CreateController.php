<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentType;

final class CreateController
{
    public function __invoke(): View
    {
        return view('tentapress-content-types::content-types.form', [
            'contentType' => new TpContentType([
                'archive_enabled' => true,
                'api_visibility' => 'disabled',
                'default_editor_driver' => 'blocks',
            ]),
            'existingTypeKeys' => TpContentType::query()->orderBy('key')->pluck('key')->all(),
            'mode' => 'create',
        ]);
    }
}
