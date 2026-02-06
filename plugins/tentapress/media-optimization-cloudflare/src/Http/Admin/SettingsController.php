<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationCloudflare\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Settings\Services\SettingsStore;

final readonly class SettingsController
{
    /**
     * @return array<string,string>
     */
    public function defaults(SettingsStore $settings): array
    {
        return [
            'optimizationCloudflareEnabled' => (string) $settings->get('optimization.cloudflare.enabled', '0'),
            'optimizationCloudflareHost' => (string) $settings->get('optimization.cloudflare.host', ''),
            'optimizationCloudflareMode' => (string) $settings->get('optimization.cloudflare.mode', 'auto'),
            'optimizationCloudflareDefaultFormat' => (string) $settings->get('optimization.cloudflare.default_format', 'auto'),
            'optimizationCloudflareDefaultQuality' => (string) $settings->get('optimization.cloudflare.default_quality', '80'),
            'optimizationCloudflareDefaultFit' => (string) $settings->get('optimization.cloudflare.default_fit', 'scale-down'),
            'optimizationCloudflareDefaultDpr' => (string) $settings->get('optimization.cloudflare.default_dpr', '1'),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'optimization_cloudflare_enabled' => ['nullable', 'string'],
            'optimization_cloudflare_host' => ['nullable', 'string', 'max:255'],
            'optimization_cloudflare_mode' => ['nullable', 'string', 'max:20'],
            'optimization_cloudflare_default_format' => ['nullable', 'string', 'max:20'],
            'optimization_cloudflare_default_quality' => ['nullable', 'string', 'max:5'],
            'optimization_cloudflare_default_fit' => ['nullable', 'string', 'max:30'],
            'optimization_cloudflare_default_dpr' => ['nullable', 'string', 'max:5'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('optimization.cloudflare.enabled', $request->has('optimization_cloudflare_enabled') ? '1' : '0', true);
        $settings->set('optimization.cloudflare.host', trim((string) ($data['optimization_cloudflare_host'] ?? '')), true);
        $settings->set('optimization.cloudflare.mode', trim((string) ($data['optimization_cloudflare_mode'] ?? 'auto')), true);
        $settings->set('optimization.cloudflare.default_format', trim((string) ($data['optimization_cloudflare_default_format'] ?? 'auto')), true);
        $settings->set('optimization.cloudflare.default_quality', trim((string) ($data['optimization_cloudflare_default_quality'] ?? '80')), true);
        $settings->set('optimization.cloudflare.default_fit', trim((string) ($data['optimization_cloudflare_default_fit'] ?? 'scale-down')), true);
        $settings->set('optimization.cloudflare.default_dpr', trim((string) ($data['optimization_cloudflare_default_dpr'] ?? '1')), true);
    }
}
