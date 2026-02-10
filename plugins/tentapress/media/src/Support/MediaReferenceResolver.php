<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;

final readonly class MediaReferenceResolver
{
    public function __construct(
        private MediaUrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @param  array<string,mixed>  $reference
     * @param  array<string,mixed>  $options
     * @return array{
     *   id:int|null,
     *   src:string,
     *   alt:string,
     *   srcset:string|null,
     *   sizes:string|null,
     *   width:int|null,
     *   height:int|null
     * }|null
     */
    public function resolveImage(array $reference, array $options = []): ?array
    {
        $media = $this->resolveMedia($reference);
        $fallbackUrl = isset($reference['url']) && is_string($reference['url']) ? trim($reference['url']) : '';
        $alt = isset($reference['alt']) && is_string($reference['alt']) ? trim($reference['alt']) : '';

        if ($media === null) {
            if ($fallbackUrl === '') {
                return null;
            }

            return [
                'id' => null,
                'src' => $fallbackUrl,
                'alt' => $alt,
                'srcset' => null,
                'sizes' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $variant = isset($options['variant']) && is_string($options['variant']) ? trim($options['variant']) : 'large';
        if ($variant === '') {
            $variant = 'large';
        }

        $src = $this->urlGenerator->imageUrl($media, ['variant' => $variant]) ?? $this->urlGenerator->url($media);
        if ($src === null) {
            return null;
        }

        $srcset = $this->buildSrcSet($media);
        $sizes = isset($options['sizes']) && is_string($options['sizes']) ? trim($options['sizes']) : '';
        if ($sizes === '') {
            $sizes = $srcset !== null ? '(min-width: 1024px) 1024px, 100vw' : '';
        }

        $dimensions = $this->dimensionsForVariant($media, $variant);

        if ($alt === '') {
            $alt = trim((string) ($media->alt_text ?? ''));
            if ($alt === '') {
                $alt = trim((string) ($media->title ?? ''));
            }
        }

        return [
            'id' => (int) $media->id,
            'src' => $src,
            'alt' => $alt,
            'srcset' => $srcset,
            'sizes' => $sizes !== '' ? $sizes : null,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
        ];
    }

    /**
     * @param  array<string,mixed>  $reference
     */
    private function resolveMedia(array $reference): ?TpMedia
    {
        $mediaId = null;

        if (isset($reference['media_id']) && is_numeric($reference['media_id'])) {
            $mediaId = (int) $reference['media_id'];
        } elseif (isset($reference['mediaId']) && is_numeric($reference['mediaId'])) {
            $mediaId = (int) $reference['mediaId'];
        } elseif (isset($reference['id']) && is_numeric($reference['id'])) {
            $mediaId = (int) $reference['id'];
        }

        if ($mediaId !== null && $mediaId > 0) {
            $item = TpMedia::query()->find($mediaId);
            if ($item instanceof TpMedia) {
                return $item;
            }
        }

        $url = isset($reference['url']) && is_string($reference['url']) ? trim($reference['url']) : '';
        if ($url === '') {
            return null;
        }

        $path = $this->pathFromPublicUrl($url);
        if ($path === null) {
            return null;
        }

        $item = TpMedia::query()
            ->where('disk', 'public')
            ->where('path', $path)
            ->first();

        return $item instanceof TpMedia ? $item : null;
    }

    private function pathFromPublicUrl(string $url): ?string
    {
        $path = $url;
        if (str_contains($url, '://')) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            if (! is_string($parsedPath)) {
                return null;
            }
            $path = $parsedPath;
        }

        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        $normalized = ltrim(substr($path, strlen('/storage/')), '/');

        return $normalized !== '' ? $normalized : null;
    }

    private function buildSrcSet(TpMedia $media): ?string
    {
        $variants = is_array($media->variants) ? $media->variants : [];
        if ($variants === []) {
            return null;
        }

        $entries = [];
        foreach ($variants as $key => $variant) {
            if (! is_string($key) || ! is_array($variant)) {
                continue;
            }

            $width = isset($variant['width']) && is_numeric($variant['width']) ? (int) $variant['width'] : 0;
            if ($width < 1) {
                continue;
            }

            $url = $this->urlGenerator->imageUrl($media, ['variant' => $key]);
            if ($url === null) {
                continue;
            }

            $entries[$width] = $url.' '.$width.'w';
        }

        if ($entries === []) {
            return null;
        }

        ksort($entries);

        return implode(', ', array_values($entries));
    }

    /**
     * @return array{width:int|null,height:int|null}
     */
    private function dimensionsForVariant(TpMedia $media, string $variant): array
    {
        $variants = is_array($media->variants) ? $media->variants : [];

        if (isset($variants[$variant]) && is_array($variants[$variant])) {
            $width = isset($variants[$variant]['width']) && is_numeric($variants[$variant]['width'])
                ? (int) $variants[$variant]['width']
                : null;
            $height = isset($variants[$variant]['height']) && is_numeric($variants[$variant]['height'])
                ? (int) $variants[$variant]['height']
                : null;

            if ($width !== null && $height !== null) {
                return ['width' => $width, 'height' => $height];
            }
        }

        $width = isset($media->width) && is_numeric($media->width) ? (int) $media->width : null;
        $height = isset($media->height) && is_numeric($media->height) ? (int) $media->height : null;

        return ['width' => $width, 'height' => $height];
    }
}
