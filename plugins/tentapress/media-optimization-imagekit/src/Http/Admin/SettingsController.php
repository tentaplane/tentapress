<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImageKit\Http\Admin;

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
            'optimizationImageKitEnabled' => (string) $settings->get('optimization.imagekit.enabled', '0'),
            'optimizationImageKitEndpoint' => (string) $settings->get('optimization.imagekit.endpoint', ''),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'optimization_imagekit_enabled' => ['nullable', 'string'],
            'optimization_imagekit_endpoint' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('optimization.imagekit.enabled', $request->has('optimization_imagekit_enabled') ? '1' : '0', true);
        $settings->set('optimization.imagekit.endpoint', trim((string) ($data['optimization_imagekit_endpoint'] ?? '')), true);
    }
}
