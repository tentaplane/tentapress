<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Stock;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use TentaPress\Media\Support\MediaFeatureAvailability;
use TentaPress\Settings\Services\SettingsStore;

final class SettingsController
{
    public function edit(SettingsStore $settings, MediaFeatureAvailability $features): View|RedirectResponse
    {
        if (! $features->hasStockSources()) {
            return to_route('tp.media.index')
                ->with('tp_notice_warning', 'No stock source plugins are enabled.');
        }

        return view('tentapress-media::media.stock-settings', [
            'stockAttributionReminder' => (string) $settings->get('stock.attribution.reminder', '1'),
            'providerSettings' => $this->providerSettings($features),
            'providerSettingsData' => $this->providerSettingsData($settings, $features),
        ]);
    }

    public function update(Request $request, SettingsStore $settings, MediaFeatureAvailability $features): RedirectResponse
    {
        if (! $features->hasStockSources()) {
            return to_route('tp.media.index')
                ->with('tp_notice_warning', 'No stock source plugins are enabled.');
        }

        $request->validate([
            'stock_attribution_reminder' => ['nullable', 'string'],
        ]);

        $this->persistProviderSettings($request, $settings, $features);
        $settings->set('stock.attribution.reminder', $request->has('stock_attribution_reminder') ? '1' : '0', true);

        return to_route('tp.media.stock.settings')
            ->with('tp_notice_success', 'Stock settings saved.');
    }

    /**
     * @return array<int,string>
     */
    private function providerSettings(MediaFeatureAvailability $features): array
    {
        $views = [];

        if ($features->isEnabled('tentapress/media-stock-unsplash') && view()->exists('tentapress-media-stock-unsplash::settings')) {
            $views[] = 'tentapress-media-stock-unsplash::settings';
        }

        if ($features->isEnabled('tentapress/media-stock-pexels') && view()->exists('tentapress-media-stock-pexels::settings')) {
            $views[] = 'tentapress-media-stock-pexels::settings';
        }

        return $views;
    }

    /**
     * @return array<string,string>
     */
    private function providerSettingsData(SettingsStore $settings, MediaFeatureAvailability $features): array
    {
        $data = [];

        if ($features->isEnabled('tentapress/media-stock-unsplash') && class_exists(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if ($features->isEnabled('tentapress/media-stock-pexels') && class_exists(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        return $data;
    }

    private function persistProviderSettings(Request $request, SettingsStore $settings, MediaFeatureAvailability $features): void
    {
        if ($features->isEnabled('tentapress/media-stock-unsplash') && class_exists(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaStockUnsplash\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if ($features->isEnabled('tentapress/media-stock-pexels') && class_exists(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaStockPexels\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }
    }

}
