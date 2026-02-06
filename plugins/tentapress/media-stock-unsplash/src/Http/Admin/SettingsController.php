<?php

declare(strict_types=1);

namespace TentaPress\MediaStockUnsplash\Http\Admin;

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
            'stockUnsplashEnabled' => (string) $settings->get('stock.unsplash.enabled', '1'),
            'stockUnsplashKey' => (string) $settings->get('stock.unsplash.key', ''),
        ];
    }

    /**
     * @return array<string,string>
     */
    public function validate(Request $request): array
    {
        return $request->validate([
            'stock_unsplash_enabled' => ['nullable', 'string'],
            'stock_unsplash_key' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param array<string,string> $data
     */
    public function persist(Request $request, SettingsStore $settings, array $data): void
    {
        $settings->set('stock.unsplash.enabled', $request->has('stock_unsplash_enabled') ? '1' : '0', true);
        $settings->set('stock.unsplash.key', trim((string) ($data['stock_unsplash_key'] ?? '')), true);
    }
}
