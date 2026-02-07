<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Stock;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockSource;
use TentaPress\Media\Stock\StockResult;
use TentaPress\Media\Support\LocalImageVariantProcessor;
use TentaPress\Media\Support\MediaFeatureAvailability;
use Throwable;

final class ImportController
{
    public function __invoke(
        Request $request,
        StockManager $manager,
        MediaFeatureAvailability $features,
        LocalImageVariantProcessor $processor,
    ): RedirectResponse|JsonResponse {
        if (! $features->hasStockSources()) {
            return $this->respondError($request, 'No stock source plugins are enabled.', 422, true);
        }

        $data = $request->validate([
            'source' => ['required', 'string'],
            'id' => ['required', 'string'],
            'media_type' => ['nullable', 'string'],
        ]);

        $sourceKey = (string) $data['source'];
        $source = $manager->get($sourceKey);
        if ($source === null || ! $source->isEnabled()) {
            return $this->respondError($request, 'Stock source is not available.');
        }

        $mediaType = isset($data['media_type']) ? (string) $data['media_type'] : null;
        try {
            $result = $source->find((string) $data['id'], $mediaType);
        } catch (Throwable) {
            return $this->respondError($request, 'Unable to reach stock provider (offline?).');
        }
        if ($result === null || $result->downloadUrl === null || $result->downloadUrl === '') {
            return $this->respondError($request, 'Unable to download this asset.');
        }

        $downloadUrl = $this->resolveDownloadUrl($source, $result);
        if ($downloadUrl === null) {
            return $this->respondError($request, 'Unable to resolve download URL.');
        }
        if (! $this->isAllowedDownloadUrl($downloadUrl)) {
            return $this->respondError($request, 'Download URL is not allowed.');
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(20)
                ->withOptions(['stream' => true])
                ->get($downloadUrl);
        } catch (Throwable) {
            return $this->respondError($request, 'Download failed (offline?).');
        }
        if (! $response->ok()) {
            return $this->respondError($request, 'Download failed.');
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
        $processed = $processor->process('public', $path, $mimeType);

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
            'size' => $processed['size'] ?? Storage::disk('public')->size($path),
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

        return $this->respondSuccess($request, (int) $media->id);
    }

    private function respondError(
        Request $request,
        string $message,
        int $status = 422,
        bool $redirectToMedia = false,
    ): RedirectResponse|JsonResponse {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], $status);
        }

        if ($redirectToMedia) {
            return to_route('tp.media.index')
                ->with('tp_notice_warning', $message);
        }

        return back()->with('tp_notice_error', $message);
    }

    private function respondSuccess(Request $request, int $mediaId): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Stock asset imported.',
                'media' => [
                    'id' => $mediaId,
                    'edit_url' => route('tp.media.edit', ['media' => $mediaId]),
                ],
            ]);
        }

        return to_route('tp.media.edit', ['media' => $mediaId])
            ->with('tp_notice_success', 'Stock asset imported.');
    }

    private function resolveDownloadUrl(object $source, StockResult $result): ?string
    {
        if (! $source instanceof StockSource) {
            return $result->downloadUrl;
        }

        $resolved = $source->resolveDownloadUrl($result);
        if (is_string($resolved) && $resolved !== '') {
            return $resolved;
        }

        return $result->downloadUrl;
    }

    private function isAllowedDownloadUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(trim((string) ($parts['host'] ?? '')));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublicIp($host);
        }

        $resolvedIps = gethostbynamel($host);
        if (! is_array($resolvedIps) || $resolvedIps === []) {
            $dnsRecords = function_exists('dns_get_record')
                ? dns_get_record($host, DNS_A + DNS_AAAA)
                : [];

            $resolvedIps = [];
            if (is_array($dnsRecords)) {
                foreach ($dnsRecords as $record) {
                    if (! is_array($record)) {
                        continue;
                    }

                    $ip = $record['ip'] ?? $record['ipv6'] ?? null;
                    if (is_string($ip) && $ip !== '') {
                        $resolvedIps[] = $ip;
                    }
                }
            }
        }

        if ($resolvedIps === []) {
            return false;
        }

        foreach ($resolvedIps as $ip) {
            if (! is_string($ip) || ! $this->isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
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

}
