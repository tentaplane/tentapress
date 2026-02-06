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
            'stockAttributionReminder' => (string) $settings->get('stock.attribution.reminder', '1'),
            'providerSettings' => $this->providerSettings($settings),
            'providerSettingsData' => $this->providerSettingsData($settings),
        ]);
    }

    public function update(Request $request, SettingsStore $settings): RedirectResponse
    {
        $request->validate([
            'stock_attribution_reminder' => ['nullable', 'string'],
        ]);

        $this->persistProviderSettings($request, $settings);
        $settings->set('stock.attribution.reminder', $request->has('stock_attribution_reminder') ? '1' : '0', true);

        return to_route('tp.media.stock.settings')
            ->with('tp_notice_success', 'Stock settings saved.');
    }

    /**
     * @return array<int,string>
     */
    private function providerSettings(SettingsStore $settings): array
    {
        $views = [];

        if (view()->exists('tentapress-media-stock-unsplash::settings')) {
            $views[] = 'tentapress-media-stock-unsplash::settings';
        }

        if (view()->exists('tentapress-media-stock-pexels::settings')) {
            $views[] = 'tentapress-media-stock-pexels::settings';
        }

        return $views;
    }

    /**
     * @return array<string,string>
     */
    private function providerSettingsData(SettingsStore $settings): array
    {
        $data = [];

        if (class_exists(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class)) {
            $controller = app(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if (class_exists(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class)) {
            $controller = app(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        return $data;
    }

    private function persistProviderSettings(Request $request, SettingsStore $settings): void
    {
        if (class_exists(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class)) {
            $controller = app(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if (class_exists(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class)) {
            $controller = app(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }
    }

}
