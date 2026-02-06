<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock\Sources;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use TentaPress\Media\Stock\StockQuery;
use TentaPress\Media\Stock\StockResult;
use TentaPress\Media\Stock\StockSearchResults;
use TentaPress\Media\Stock\StockSettings;
use TentaPress\Media\Stock\StockSource;
use Throwable;

final readonly class PexelsSource implements StockSource
{
    private const PHOTO_URL = 'https://api.pexels.com/v1';
    private const VIDEO_URL = 'https://api.pexels.com/videos';

    public function __construct(private StockSettings $settings)
    {
    }

    public function key(): string
    {
        return 'pexels';
    }

    public function label(): string
    {
        return 'Pexels';
    }

    public function supportedMediaTypes(): array
    {
        return $this->settings->pexelsVideoEnabled() ? ['image', 'video'] : ['image'];
    }

    public function isEnabled(): bool
    {
        return $this->settings->pexelsEnabled() && $this->settings->pexelsKey() !== '';
    }

    public function search(StockQuery $query): StockSearchResults
    {
        $mediaType = $query->mediaType === 'video' && $this->settings->pexelsVideoEnabled() ? 'video' : 'image';
        $sort = $this->resolveSort($query->sort);
        $cacheKey = sprintf(
            'tp-stock-pexels:%s:%s:%s:%d:%d',
            $mediaType,
            $sort ?? 'relevant',
            md5($query->query),
            $query->page,
            $query->perPage
        );

        return Cache::remember($cacheKey, 60, function () use ($query, $mediaType, $sort): StockSearchResults {
            $endpoint = $this->resolveEndpoint($mediaType, $sort);
            try {
                $payload = [
                    'query' => $query->query,
                    'page' => $query->page,
                    'per_page' => $query->perPage,
                ];

                if ($mediaType === 'image' && $sort === null) {
                    $orientation = $this->resolveOrientation($query->orientation);
                    if ($orientation !== null) {
                        $payload['orientation'] = $orientation;
                    }
                }

                if ($sort === 'popular') {
                    unset($payload['query']);
                }

                $response = Http::withHeaders($this->headers())
                    ->get($endpoint, $payload);
            } catch (Throwable) {
                return new StockSearchResults([], $query->page, $query->perPage, null, false, true);
            }

            if (! $response->ok()) {
                return new StockSearchResults([], $query->page, $query->perPage, null, false);
            }

            $payload = $response->json();
            $results = is_array($payload)
                ? ($mediaType === 'video' ? ($payload['videos'] ?? []) : ($payload['photos'] ?? []))
                : [];

            $items = [];
            foreach ($results as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $id = (string) ($item['id'] ?? '');
                if ($id === '') {
                    continue;
                }

                $author = (string) ($item['photographer'] ?? $item['user']['name'] ?? 'Unknown');
                $authorUrl = isset($item['photographer_url'])
                    ? (string) $item['photographer_url']
                    : (isset($item['user']['url']) ? (string) $item['user']['url'] : null);

                $title = (string) ($item['alt'] ?? 'Untitled');
                $sourceUrl = isset($item['url']) ? (string) $item['url'] : null;
                $previewUrl = null;
                $downloadUrl = null;
                $width = isset($item['width']) ? (int) $item['width'] : null;
                $height = isset($item['height']) ? (int) $item['height'] : null;

                if ($mediaType === 'video') {
                    $previewUrl = isset($item['image']) ? (string) $item['image'] : null;
                    $downloadUrl = $this->pickVideoFile($item);
                    $width = isset($item['width']) ? (int) $item['width'] : null;
                    $height = isset($item['height']) ? (int) $item['height'] : null;
                } else {
                    $previewUrl = isset($item['src']['medium']) ? (string) $item['src']['medium'] : null;
                    $downloadUrl = isset($item['src']['original']) ? (string) $item['src']['original'] : null;
                }

                $items[] = new StockResult(
                    id: $id,
                    provider: $this->key(),
                    title: $title,
                    author: $author,
                    authorUrl: $authorUrl,
                    sourceUrl: $sourceUrl,
                    license: 'PEXELS',
                    licenseUrl: 'https://www.pexels.com/license/',
                    previewUrl: $previewUrl,
                    downloadUrl: $downloadUrl,
                    width: $width,
                    height: $height,
                    mediaType: $mediaType,
                    attribution: $this->buildAttributionText($author, $mediaType),
                    attributionHtml: $this->buildAttributionHtml($author, $mediaType),
                    meta: $item,
                );
            }

            $total = isset($payload['total_results']) ? (int) $payload['total_results'] : null;
            $hasMore = isset($payload['next_page']) ? (string) $payload['next_page'] !== '' : false;

            return new StockSearchResults($items, $query->page, $query->perPage, $total, $hasMore);
        });
    }

    public function find(string $id, ?string $mediaType = null): ?StockResult
    {
        $mediaType = $mediaType === 'video' && $this->settings->pexelsVideoEnabled() ? 'video' : 'image';
        $endpoint = $mediaType === 'video'
            ? self::VIDEO_URL.'/videos/'.$id
            : self::PHOTO_URL.'/photos/'.$id;

        try {
            $response = Http::withHeaders($this->headers())->get($endpoint);
        } catch (Throwable) {
            return null;
        }
        if (! $response->ok()) {
            return null;
        }

        $item = $response->json();
        if (! is_array($item)) {
            return null;
        }

        $author = (string) ($item['photographer'] ?? $item['user']['name'] ?? 'Unknown');
        $authorUrl = isset($item['photographer_url'])
            ? (string) $item['photographer_url']
            : (isset($item['user']['url']) ? (string) $item['user']['url'] : null);

        $title = (string) ($item['alt'] ?? 'Untitled');
        $sourceUrl = isset($item['url']) ? (string) $item['url'] : null;
        $previewUrl = null;
        $downloadUrl = null;
        $width = isset($item['width']) ? (int) $item['width'] : null;
        $height = isset($item['height']) ? (int) $item['height'] : null;

        if ($mediaType === 'video') {
            $previewUrl = isset($item['image']) ? (string) $item['image'] : null;
            $downloadUrl = $this->pickVideoFile($item);
        } else {
            $previewUrl = isset($item['src']['medium']) ? (string) $item['src']['medium'] : null;
            $downloadUrl = isset($item['src']['original']) ? (string) $item['src']['original'] : null;
        }

        return new StockResult(
            id: (string) ($item['id'] ?? $id),
            provider: $this->key(),
            title: $title,
            author: $author,
            authorUrl: $authorUrl,
            sourceUrl: $sourceUrl,
            license: 'PEXELS',
            licenseUrl: 'https://www.pexels.com/license/',
            previewUrl: $previewUrl,
            downloadUrl: $downloadUrl,
            width: $width,
            height: $height,
            mediaType: $mediaType,
            attribution: $this->buildAttributionText($author, $mediaType),
            attributionHtml: $this->buildAttributionHtml($author, $mediaType),
            meta: $item,
        );
    }

    /**
     * @return array<string,string>
     */
    private function headers(): array
    {
        return [
            'Authorization' => $this->settings->pexelsKey(),
        ];
    }

    private function buildAttributionText(string $author, string $mediaType): string
    {
        $label = $mediaType === 'video' ? 'Video' : 'Photo';

        return sprintf('%s by %s on Pexels', $label, $author);
    }

    private function buildAttributionHtml(string $author, string $mediaType): string
    {
        $label = $mediaType === 'video' ? 'Video' : 'Photo';

        return sprintf('%s by %s on <a href="https://www.pexels.com">Pexels</a>', $label, $author);
    }

    private function pickVideoFile(array $item): ?string
    {
        $files = $item['video_files'] ?? null;
        if (! is_array($files) || $files === []) {
            return null;
        }

        usort($files, static function ($a, $b): int {
            $aWidth = isset($a['width']) ? (int) $a['width'] : 0;
            $bWidth = isset($b['width']) ? (int) $b['width'] : 0;

            return $bWidth <=> $aWidth;
        });

        $best = $files[0] ?? null;
        if (! is_array($best)) {
            return null;
        }

        return isset($best['link']) ? (string) $best['link'] : null;
    }

    private function resolveSort(?string $sort): ?string
    {
        if ($sort === null || $sort === '') {
            return null;
        }

        $normalized = strtolower($sort);
        if ($normalized === 'popular') {
            return 'popular';
        }

        return null;
    }

    private function resolveEndpoint(string $mediaType, ?string $sort): string
    {
        if ($mediaType === 'video') {
            return $sort === 'popular' ? self::VIDEO_URL.'/popular' : self::VIDEO_URL.'/search';
        }

        return $sort === 'popular' ? self::PHOTO_URL.'/curated' : self::PHOTO_URL.'/search';
    }

    private function resolveOrientation(?string $orientation): ?string
    {
        if ($orientation === null || $orientation === '') {
            return null;
        }

        $normalized = strtolower($orientation);
        if (in_array($normalized, ['landscape', 'portrait', 'square'], true)) {
            return $normalized;
        }

        return null;
    }
}
