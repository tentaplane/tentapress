<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationCloudflare\Optimization;

use TentaPress\Media\Optimization\OptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final readonly class CloudflareOptimizationProvider implements OptimizationProvider
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function key(): string
    {
        return 'cloudflare';
    }

    public function label(): string
    {
        return 'Cloudflare Images';
    }

    public function isEnabled(): bool
    {
        return $this->settings->get('optimization.cloudflare.enabled', '0') === '1';
    }

    public function imageUrl(string $sourceUrl, array $params = []): ?string
    {
        $sourceUrl = trim($sourceUrl);

        if ($sourceUrl === '') {
            return null;
        }

        $parts = parse_url($sourceUrl);
        if (! is_array($parts) || ($parts['host'] ?? '') === '') {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $sourceHost = (string) ($parts['host'] ?? '');
        $host = trim((string) $this->settings->get('optimization.cloudflare.host', ''));
        $host = $host !== '' ? $host : $sourceHost;
        $base = $scheme.'://'.$host;

        $mode = $this->resolveMode($sourceHost, $host);
        $options = $this->buildOptions($params);

        if ($options === '') {
            return $sourceUrl;
        }

        if ($mode === 'path' && $sourceHost === $host) {
            $path = $parts['path'] ?? '/';
            $query = $parts['query'] ?? '';
            $target = $path.($query !== '' ? '?'.$query : '');

            if (! str_starts_with($target, '/')) {
                $target = '/'.$target;
            }

            return $base.'/cdn-cgi/image/'.$options.$target;
        }

        return $base.'/cdn-cgi/image/'.$options.'/'.$sourceUrl;
    }

    private function resolveMode(string $sourceHost, string $host): string
    {
        $mode = strtolower(trim((string) $this->settings->get('optimization.cloudflare.mode', 'auto')));

        if (in_array($mode, ['path', 'absolute'], true)) {
            return $mode;
        }

        return $sourceHost === $host ? 'path' : 'absolute';
    }

    /**
     * @param array<string, scalar> $params
     */
    private function buildOptions(array $params): string
    {
        $defaults = [
            'format' => $this->settings->get('optimization.cloudflare.default_format', 'auto'),
            'quality' => $this->settings->get('optimization.cloudflare.default_quality', '80'),
            'fit' => $this->settings->get('optimization.cloudflare.default_fit', 'scale-down'),
            'dpr' => $this->settings->get('optimization.cloudflare.default_dpr', '1'),
        ];

        $keys = ['width', 'height', 'fit', 'quality', 'format', 'dpr'];
        $options = [];

        foreach ($keys as $key) {
            $value = $params[$key] ?? $defaults[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $options[] = $key.'='.$value;
        }

        return implode(',', $options);
    }
}
