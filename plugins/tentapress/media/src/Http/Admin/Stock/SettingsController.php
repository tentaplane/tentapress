<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Stock;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use TentaPress\Settings\Services\SettingsStore;

final class SettingsController
{
    public function edit(SettingsStore $settings): View
    {
        return view('tentapress-media::media.stock-settings', [
            'stockUnsplashEnabled' => (string) $settings->get('stock.unsplash.enabled', '1'),
            'stockUnsplashKey' => (string) $settings->get('stock.unsplash.key', ''),
            'stockPexelsEnabled' => (string) $settings->get('stock.pexels.enabled', '1'),
            'stockPexelsKey' => (string) $settings->get('stock.pexels.key', ''),
            'stockPexelsVideoEnabled' => (string) $settings->get('stock.pexels.video_enabled', '1'),
            'stockAttributionReminder' => (string) $settings->get('stock.attribution.reminder', '1'),
        ]);
    }

    public function update(Request $request, SettingsStore $settings): RedirectResponse
    {
        $data = $request->validate([
            'stock_unsplash_enabled' => ['nullable', 'string'],
            'stock_unsplash_key' => ['nullable', 'string', 'max:255'],
            'stock_pexels_enabled' => ['nullable', 'string'],
            'stock_pexels_key' => ['nullable', 'string', 'max:255'],
            'stock_pexels_video_enabled' => ['nullable', 'string'],
            'stock_attribution_reminder' => ['nullable', 'string'],
        ]);

        $settings->set('stock.unsplash.enabled', $request->has('stock_unsplash_enabled') ? '1' : '0', true);
        $settings->set('stock.unsplash.key', trim((string) ($data['stock_unsplash_key'] ?? '')), true);

        $settings->set('stock.pexels.enabled', $request->has('stock_pexels_enabled') ? '1' : '0', true);
        $settings->set('stock.pexels.key', trim((string) ($data['stock_pexels_key'] ?? '')), true);
        $settings->set('stock.pexels.video_enabled', $request->has('stock_pexels_video_enabled') ? '1' : '0', true);

        $settings->set('stock.attribution.reminder', $request->has('stock_attribution_reminder') ? '1' : '0', true);

        return to_route('tp.media.stock.settings')
            ->with('tp_notice_success', 'Stock settings saved.');
    }
}
