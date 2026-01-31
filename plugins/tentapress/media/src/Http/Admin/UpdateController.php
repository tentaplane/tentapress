<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use TentaPress\Media\Http\Requests\UpdateMediaRequest;
use TentaPress\Media\Models\TpMedia;

final class UpdateController
{
    public function __invoke(UpdateMediaRequest $request, TpMedia $media): RedirectResponse
    {
        $data = $request->validated();

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $media->fill([
            'title' => $data['title'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'updated_by' => $nowUserId ?: null,
        ]);

        $media->save();

        return to_route('tp.media.edit', ['media' => $media->id])
            ->with('tp_notice_success', 'Media updated.');
    }
}
