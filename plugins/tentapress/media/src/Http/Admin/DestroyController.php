<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use TentaPress\Media\Models\TpMedia;

final class DestroyController
{
    public function __invoke(TpMedia $media): RedirectResponse
    {
        $disk = $media->disk !== null && $media->disk !== '' ? $media->disk : 'public';
        $path = (string) ($media->path ?? '');

        if ($path !== '') {
            Storage::disk($disk)->delete($path);
        }

        $media->delete();

        return to_route('tp.media.index')
            ->with('tp_notice_success', 'Media deleted.');
    }
}
