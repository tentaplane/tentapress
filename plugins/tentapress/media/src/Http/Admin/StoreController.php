<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use TentaPress\Media\Http\Requests\StoreMediaRequest;
use TentaPress\Media\Models\TpMedia;

final class StoreController
{
    public function __invoke(StoreMediaRequest $request): RedirectResponse
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

        [$width, $height] = $this->imageDimensions($file);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $media = TpMedia::query()->create([
            'title' => $title !== '' ? $title : null,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
        ]);

        return to_route('tp.media.edit', ['media' => $media->id])
            ->with('tp_notice_success', 'Media uploaded.');
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function imageDimensions(UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();
        if ($mime === '' || ! str_starts_with($mime, 'image/')) {
            return [null, null];
        }

        $path = $file->getPathname();
        if ($path === '') {
            return [null, null];
        }

        $size = @getimagesize($path);
        if (! is_array($size)) {
            return [null, null];
        }

        $width = isset($size[0]) ? (int) $size[0] : null;
        $height = isset($size[1]) ? (int) $size[1] : null;

        return [$width, $height];
    }
}
