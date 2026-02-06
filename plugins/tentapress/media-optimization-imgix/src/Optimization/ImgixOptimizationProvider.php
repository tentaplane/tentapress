<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImgix\Optimization;

use TentaPress\Media\Optimization\OptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final readonly class ImgixOptimizationProvider implements OptimizationProvider
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function key(): string
    {
        return 'imgix';
    }

    public function label(): string
    {
        return 'Imgix';
    }

    public function isEnabled(): bool
    {
        return $this->settings->get('optimization.imgix.enabled', '0') === '1';
    }

    public function imageUrl(string $sourceUrl, array $params = []): ?string
    {
        $sourceUrl = trim($sourceUrl);

        if ($sourceUrl === '') {
            return null;
        }

        $host = trim((string) $this->settings->get('optimization.imgix.host', ''));
        if ($host === '') {
            return null;
        }

        $parts = parse_url($sourceUrl);
        if (! is_array($parts)) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if ($path === '') {
            return null;
        }

        $normalized = $this->normalizeParams($params);
        $query = $normalized === [] ? '' : '?'.http_build_query($normalized);

        return 'https://'.$host.$path.$query;
    }

    /**
     * @param array<string, scalar> $params
     * @return array<string, scalar>
     */
    private function normalizeParams(array $params): array
    {
        $allowed = ['width', 'height', 'quality', 'format', 'fit', 'dpr'];
        $map = [
            'width' => 'w',
            'height' => 'h',
            'quality' => 'q',
            'format' => 'fm',
            'fit' => 'fit',
            'dpr' => 'dpr',
        ];

        $normalized = [];

        foreach ($allowed as $key) {
            if (! array_key_exists($key, $params)) {
                continue;
            }

            $value = $params[$key];
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$map[$key]] = $value;
        }

        return $normalized;
    }
}
