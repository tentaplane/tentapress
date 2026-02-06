<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Stock;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockResult;
use TentaPress\Media\Stock\Sources\UnsplashSource;
use Throwable;

final class ImportController
{
    public function __invoke(Request $request, StockManager $manager): RedirectResponse
    {
        $data = $request->validate([
            'source' => ['required', 'string'],
            'id' => ['required', 'string'],
            'media_type' => ['nullable', 'string'],
        ]);

        $sourceKey = (string) $data['source'];
        $source = $manager->get($sourceKey);
        if ($source === null || ! $source->isEnabled()) {
            return back()->with('tp_notice_error', 'Stock source is not available.');
        }

        $mediaType = isset($data['media_type']) ? (string) $data['media_type'] : null;
        try {
            $result = $source->find((string) $data['id'], $mediaType);
        } catch (Throwable) {
            return back()->with('tp_notice_error', 'Unable to reach stock provider (offline?).');
        }
        if ($result === null || $result->downloadUrl === null || $result->downloadUrl === '') {
            return back()->with('tp_notice_error', 'Unable to download this asset.');
        }

        $downloadUrl = $this->resolveDownloadUrl($source, $result);
        if ($downloadUrl === null) {
            return back()->with('tp_notice_error', 'Unable to resolve download URL.');
        }

        try {
            $response = Http::withOptions(['stream' => true])->get($downloadUrl);
        } catch (Throwable) {
            return back()->with('tp_notice_error', 'Download failed (offline?).');
        }
        if (! $response->ok()) {
            return back()->with('tp_notice_error', 'Download failed.');
        }

        $extension = $this->guessExtension($response->header('Content-Type'), $downloadUrl);
        $title = trim($result->title) !== '' ? $result->title : 'stock-asset';
        $slugBase = Str::slug($title);
        $slugBase = $slugBase !== '' ? $slugBase : 'stock-asset';
        $filename = $slugBase.'-'.Str::random(6).($extension !== '' ? '.'.$extension : '');
        $dir = 'media/stock/'.now()->format('Y/m');
        $path = $dir.'/'.$filename;

        Storage::disk('public')->put($path, $response->toPsrResponse()->getBody());

        $mimeType = $response->header('Content-Type');
        $mimeType = $mimeType ? trim(explode(';', $mimeType)[0]) : null;
        [$width, $height] = $this->imageDimensions($path, $mimeType);

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $media = TpMedia::query()->create([
            'title' => $title,
            'alt_text' => null,
            'caption' => null,
            'source' => $sourceKey,
            'source_item_id' => $result->id,
            'source_url' => $result->sourceUrl,
            'license' => $result->license,
            'license_url' => $result->licenseUrl,
            'attribution' => $result->attribution,
            'attribution_html' => $result->attributionHtml,
            'stock_meta' => [
                'provider' => $result->provider,
                'author' => $result->author,
                'author_url' => $result->authorUrl,
                'media_type' => $result->mediaType,
            ],
            'disk' => 'public',
            'path' => $path,
            'original_name' => $title.($extension !== '' ? '.'.$extension : ''),
            'mime_type' => $mimeType,
            'size' => Storage::disk('public')->size($path),
            'width' => $width,
            'height' => $height,
            'created_by' => $nowUserId ?: null,
            'updated_by' => $nowUserId ?: null,
        ]);

        return to_route('tp.media.edit', ['media' => $media->id])
            ->with('tp_notice_success', 'Stock asset imported.');
    }

    private function resolveDownloadUrl(object $source, StockResult $result): ?string
    {
        if (! $source instanceof UnsplashSource) {
            return $result->downloadUrl;
        }

        return $source->resolveDownloadUrl($result);
    }

    private function guessExtension(?string $contentType, string $downloadUrl): string
    {
        $type = $contentType ? strtolower((string) $contentType) : '';
        $type = explode(';', $type)[0] ?? '';

        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
        ];

        if ($type !== '' && isset($map[$type])) {
            return $map[$type];
        }

        $path = parse_url($downloadUrl, PHP_URL_PATH);
        if (is_string($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext !== '') {
                return strtolower($ext);
            }
        }

        return '';
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function imageDimensions(string $path, ?string $mimeType): array
    {
        $mime = (string) ($mimeType ?? '');
        if ($mime === '' || ! str_starts_with($mime, 'image/')) {
            return [null, null];
        }

        $fullPath = Storage::disk('public')->path($path);
        $size = @getimagesize($fullPath);
        if (! is_array($size)) {
            return [null, null];
        }

        $width = isset($size[0]) ? (int) $size[0] : null;
        $height = isset($size[1]) ? (int) $size[1] : null;

        return [$width, $height];
    }
}
