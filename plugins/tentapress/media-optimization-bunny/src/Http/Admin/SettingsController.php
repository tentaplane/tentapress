<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationBunny\Http\Admin;

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
            'optimizationBunnyEnabled' => (string) $settings->get('optimization.bunny.enabled', '0'),
            'optimizationBunnyHost' => (string) $settings->get('optimization.bunny.host', ''),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'optimization_bunny_enabled' => ['nullable', 'string'],
            'optimization_bunny_host' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('optimization.bunny.enabled', $request->has('optimization_bunny_enabled') ? '1' : '0', true);
        $settings->set('optimization.bunny.host', trim((string) ($data['optimization_bunny_host'] ?? '')), true);
    }
}
