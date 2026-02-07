<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use TentaPress\Media\Http\Requests\StoreMediaRequest;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Support\LocalImageVariantProcessor;

final class StoreController
{
    public function __invoke(StoreMediaRequest $request, LocalImageVariantProcessor $processor): RedirectResponse
    {
        $data = $request->validated();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            $title = (string) Str::of($originalName)->beforeLast('.');
        }

        $slugBase = Str::slug($title);
        $slugBase = $slugBase !== '' ? $slugBase : 'file';

        $extension = (string) $file->extension();
        $filename = $slugBase.'-'.Str::random(6).($extension !== '' ? '.'.$extension : '');

        $dir = 'media/'.now()->format('Y/m');
        $path = $file->storePubliclyAs($dir, $filename, ['disk' => 'public']);

        $mimeType = $file->getMimeType();
        $processed = $processor->process('public', $path, $mimeType);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $media = TpMedia::query()->create([
            'title' => $title !== '' ? $title : null,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $processed['size'] ?? $file->getSize(),
            'width' => $processed['width'],
            'height' => $processed['height'],
            'source_width' => $processed['source_width'],
            'source_height' => $processed['source_height'],
            'variants' => $processed['variants'],
            'preview_variant' => $processed['preview_variant'],
            'optimization_status' => $processed['optimization_status'],
            'optimization_error' => $processed['optimization_error'],
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
        ]);

        return to_route('tp.media.edit', ['media' => $media->id])
            ->with('tp_notice_success', 'Media uploaded.');
    }

}
