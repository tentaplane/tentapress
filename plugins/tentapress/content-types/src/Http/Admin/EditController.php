<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\ContentTypes\Models\TpContentType;

final class EditController
{
    public function __invoke(TpContentType $contentType): View
    {
        $contentType->load('fields');

        return view('tentapress-content-types::content-types.form', [
            'contentType' => $contentType,
            'existingTypeKeys' => TpContentType::query()
                ->where('id', '!=', $contentType->id)
                ->orderBy('key')
                ->pluck('key')
                ->all(),
            'mode' => 'edit',
        ]);
    }
}
