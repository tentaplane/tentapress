<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\Media\Models\TpMedia;

final class EditController
{
    public function __invoke(TpMedia $media): View
    {
        return view('tentapress-media::media.form', [
            'mode' => 'edit',
            'media' => $media,
        ]);
    }
}
