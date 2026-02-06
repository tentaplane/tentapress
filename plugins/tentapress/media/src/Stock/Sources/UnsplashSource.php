<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock\Sources;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TentaPress\Media\Stock\StockQuery;
use TentaPress\Media\Stock\StockResult;
use TentaPress\Media\Stock\StockSearchResults;
use TentaPress\Media\Stock\StockSettings;
use TentaPress\Media\Stock\StockSource;
use Throwable;

final readonly class UnsplashSource implements StockSource
{
    private const BASE_URL = 'https://api.unsplash.com';

    public function __construct(private StockSettings $settings)
    {
    }

    public function key(): string
    {
        return 'unsplash';
    }

    public function label(): string
    {
        return 'Unsplash';
    }

    public function supportedMediaTypes(): array
    {
        return ['image'];
    }

    public function isEnabled(): bool
    {
        return $this->settings->unsplashEnabled() && $this->settings->unsplashKey() !== '';
    }

    public function search(StockQuery $query): StockSearchResults
    {
        $cacheKey = sprintf(
            'tp-stock-unsplash:%s:%d:%d',
            md5($query->query),
            $query->page,
            $query->perPage
        );

        return Cache::remember($cacheKey, 60, function () use ($query): StockSearchResults {
            try {
                $payload = [
                    'query' => $query->query,
                    'page' => $query->page,
                    'per_page' => $query->perPage,
                    'content_filter' => 'high',
                ];

                $orientation = $this->resolveOrientation($query->orientation);
                if ($orientation !== null) {
                    $payload['orientation'] = $orientation;
                }

                $orderBy = $this->resolveOrderBy($query->sort);
                if ($orderBy !== null) {
                    $payload['order_by'] = $orderBy;
                }

                $response = Http::withHeaders($this->headers())
                    ->get(self::BASE_URL.'/search/photos', $payload);
            } catch (Throwable) {
                return new StockSearchResults([], $query->page, $query->perPage, null, false, true);
            }

            if (! $response->ok()) {
                return new StockSearchResults([], $query->page, $query->perPage, null, false);
            }

            $payload = $response->json();
            $results = is_array($payload) ? ($payload['results'] ?? []) : [];
            $items = [];

            foreach ($results as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $id = (string) ($item['id'] ?? '');
                if ($id === '') {
                    continue;
                }

                $title = (string) ($item['description'] ?? $item['alt_description'] ?? 'Untitled');
                $author = (string) ($item['user']['name'] ?? 'Unknown');
                $authorUrl = isset($item['user']['links']['html']) ? (string) $item['user']['links']['html'] : null;
                $sourceUrl = isset($item['links']['html']) ? (string) $item['links']['html'] : null;
                $previewUrl = isset($item['urls']['small']) ? (string) $item['urls']['small'] : null;
                $downloadUrl = isset($item['links']['download_location']) ? (string) $item['links']['download_location'] : null;
                $width = isset($item['width']) ? (int) $item['width'] : null;
                $height = isset($item['height']) ? (int) $item['height'] : null;

                $items[] = new StockResult(
                    id: $id,
                    provider: $this->key(),
                    title: $title,
                    author: $author,
                    authorUrl: $authorUrl,
                    sourceUrl: $sourceUrl,
                    license: 'UNSPLASH',
                    licenseUrl: 'https://unsplash.com/license',
                    previewUrl: $previewUrl,
                    downloadUrl: $downloadUrl,
                    width: $width,
                    height: $height,
                    mediaType: 'image',
                    attribution: $this->buildAttributionText($author, $authorUrl),
                    attributionHtml: $this->buildAttributionHtml($author, $authorUrl),
                    meta: $item,
                );
            }

            $total = isset($payload['total']) ? (int) $payload['total'] : null;
            $hasMore = isset($payload['total_pages']) ? $query->page < (int) $payload['total_pages'] : false;

            return new StockSearchResults($items, $query->page, $query->perPage, $total, $hasMore);
        });
    }

    public function find(string $id, ?string $mediaType = null): ?StockResult
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get(self::BASE_URL.'/photos/'.$id);
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

        $title = (string) ($item['description'] ?? $item['alt_description'] ?? 'Untitled');
        $author = (string) ($item['user']['name'] ?? 'Unknown');
        $authorUrl = isset($item['user']['links']['html']) ? (string) $item['user']['links']['html'] : null;
        $sourceUrl = isset($item['links']['html']) ? (string) $item['links']['html'] : null;
        $previewUrl = isset($item['urls']['small']) ? (string) $item['urls']['small'] : null;
        $downloadUrl = isset($item['links']['download_location']) ? (string) $item['links']['download_location'] : null;
        $width = isset($item['width']) ? (int) $item['width'] : null;
        $height = isset($item['height']) ? (int) $item['height'] : null;

        return new StockResult(
            id: (string) ($item['id'] ?? $id),
            provider: $this->key(),
            title: $title,
            author: $author,
            authorUrl: $authorUrl,
            sourceUrl: $sourceUrl,
            license: 'UNSPLASH',
            licenseUrl: 'https://unsplash.com/license',
            previewUrl: $previewUrl,
            downloadUrl: $downloadUrl,
            width: $width,
            height: $height,
            mediaType: 'image',
            attribution: $this->buildAttributionText($author, $authorUrl),
            attributionHtml: $this->buildAttributionHtml($author, $authorUrl),
            meta: $item,
        );
    }

    public function resolveDownloadUrl(StockResult $result): ?string
    {
        $downloadLocation = $result->downloadUrl;
        if ($downloadLocation === null || $downloadLocation === '') {
            return null;
        }

        try {
            $response = Http::withHeaders($this->headers())->get($downloadLocation);
        } catch (Throwable) {
            return null;
        }
        if (! $response->ok()) {
            return null;
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return null;
        }

        return isset($payload['url']) ? (string) $payload['url'] : null;
    }

    /**
     * @return array<string,string>
     */
    private function headers(): array
    {
        return [
            'Accept-Version' => 'v1',
            'Authorization' => 'Client-ID '.$this->settings->unsplashKey(),
        ];
    }

    private function buildAttributionText(string $author, ?string $authorUrl): string
    {
        $authorLink = $authorUrl ? $this->withUtm($authorUrl) : $author;
        $unsplashLink = $this->withUtm('https://unsplash.com');

        return sprintf('Photo by %s on Unsplash (%s)', $author, $unsplashLink).' · '.$authorLink;
    }

    private function buildAttributionHtml(string $author, ?string $authorUrl): string
    {
        $authorLink = $authorUrl ? $this->withUtm($authorUrl) : null;
        $unsplashLink = $this->withUtm('https://unsplash.com');

        if ($authorLink === null) {
            return sprintf('Photo by %s on <a href="%s">Unsplash</a>', $author, $unsplashLink);
        }

        return sprintf(
            'Photo by <a href="%s">%s</a> on <a href="%s">Unsplash</a>',
            $authorLink,
            $author,
            $unsplashLink
        );
    }

    private function withUtm(string $url): string
    {
        $separator = Str::contains($url, '?') ? '&' : '?';

        return $url.$separator.'utm_source=tentapress&utm_medium=referral';
    }

    private function resolveOrientation(?string $orientation): ?string
    {
        if ($orientation === null || $orientation === '') {
            return null;
        }

        $normalized = strtolower($orientation);
        if ($normalized === 'square') {
            return 'squarish';
        }

        if (in_array($normalized, ['landscape', 'portrait', 'squarish'], true)) {
            return $normalized;
        }

        return null;
    }

    private function resolveOrderBy(?string $sort): ?string
    {
        if ($sort === null || $sort === '') {
            return null;
        }

        $normalized = strtolower($sort);
        if (in_array($normalized, ['relevant', 'latest'], true)) {
            return $normalized;
        }

        return null;
    }
}
