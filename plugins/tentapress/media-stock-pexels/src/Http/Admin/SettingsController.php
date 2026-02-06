<?php

declare(strict_types=1);

namespace TentaPress\MediaStockPexels\Http\Admin;

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
            'stockPexelsEnabled' => (string) $settings->get('stock.pexels.enabled', '1'),
            'stockPexelsKey' => (string) $settings->get('stock.pexels.key', ''),
            'stockPexelsVideoEnabled' => (string) $settings->get('stock.pexels.video_enabled', '1'),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'stock_pexels_enabled' => ['nullable', 'string'],
            'stock_pexels_key' => ['nullable', 'string', 'max:255'],
            'stock_pexels_video_enabled' => ['nullable', 'string'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('stock.pexels.enabled', $request->has('stock_pexels_enabled') ? '1' : '0', true);
        $settings->set('stock.pexels.key', trim((string) ($data['stock_pexels_key'] ?? '')), true);
        $settings->set('stock.pexels.video_enabled', $request->has('stock_pexels_video_enabled') ? '1' : '0', true);
    }
}
