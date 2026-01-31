<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\Media\Models\TpMedia;

final class CreateController
{
    public function __invoke(): View
    {
        $media = new TpMedia([
            'title' => '',
            'alt_text' => '',
            'caption' => '',
        ]);

        return view('tentapress-media::media.form', [
            'mode' => 'create',
            'media' => $media,
        ]);
    }
}
