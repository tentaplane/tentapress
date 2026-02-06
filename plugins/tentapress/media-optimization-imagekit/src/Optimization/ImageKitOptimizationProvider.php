<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImageKit\Optimization;

use TentaPress\Media\Optimization\OptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final readonly class ImageKitOptimizationProvider implements OptimizationProvider
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function key(): string
    {
        return 'imagekit';
    }

    public function label(): string
    {
        return 'ImageKit';
    }

    public function isEnabled(): bool
    {
        return $this->settings->get('optimization.imagekit.enabled', '0') === '1';
    }

    public function imageUrl(string $sourceUrl, array $params = []): ?string
    {
        $sourceUrl = trim($sourceUrl);

        if ($sourceUrl === '') {
            return null;
        }

        $endpoint = trim((string) $this->settings->get('optimization.imagekit.endpoint', ''));
        if ($endpoint === '') {
            return null;
        }

        $endpoint = rtrim($endpoint, '/');
        $parts = parse_url($sourceUrl);
        if (! is_array($parts)) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if ($path === '') {
            return null;
        }

        $transform = $this->buildTransform($params);
        $url = $endpoint.$path;

        if ($transform !== '') {
            $url .= '?tr='.$transform;
        }

        return $url;
    }

    /**
     * @param array<string, scalar> $params
     */
    private function buildTransform(array $params): string
    {
        $map = [
            'width' => 'w',
            'height' => 'h',
            'quality' => 'q',
            'format' => 'f',
            'dpr' => 'dpr',
        ];

        $tokens = [];

        foreach ($map as $key => $token) {
            $value = $params[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $tokens[] = $token.'-'.$value;
        }

        return implode(',', $tokens);
    }
}
