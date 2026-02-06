<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImgix\Http\Admin;

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
            'optimizationImgixEnabled' => (string) $settings->get('optimization.imgix.enabled', '0'),
            'optimizationImgixHost' => (string) $settings->get('optimization.imgix.host', ''),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'optimization_imgix_enabled' => ['nullable', 'string'],
            'optimization_imgix_host' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('optimization.imgix.enabled', $request->has('optimization_imgix_enabled') ? '1' : '0', true);
        $settings->set('optimization.imgix.host', trim((string) ($data['optimization_imgix_host'] ?? '')), true);
    }
}
