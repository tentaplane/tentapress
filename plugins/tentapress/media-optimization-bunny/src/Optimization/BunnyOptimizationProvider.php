<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationBunny\Optimization;

use TentaPress\Media\Optimization\OptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final readonly class BunnyOptimizationProvider implements OptimizationProvider
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function key(): string
    {
        return 'bunny';
    }

    public function label(): string
    {
        return 'Bunny Optimizer';
    }

    public function isEnabled(): bool
    {
        return $this->settings->get('optimization.bunny.enabled', '0') === '1';
    }

    public function imageUrl(string $sourceUrl, array $params = []): ?string
    {
        $sourceUrl = trim($sourceUrl);

        if ($sourceUrl === '') {
            return null;
        }

        $host = trim((string) $this->settings->get('optimization.bunny.host', ''));
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
        $map = [
            'width' => 'width',
            'height' => 'height',
            'quality' => 'quality',
            'format' => 'format',
        ];

        $normalized = [];

        foreach ($map as $key => $param) {
            $value = $params[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$param] = $value;
        }

        return $normalized;
    }
}
