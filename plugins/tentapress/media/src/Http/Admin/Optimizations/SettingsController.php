<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Optimizations;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\Media\Optimization\OptimizationSettings;
use TentaPress\Settings\Services\SettingsStore;

final readonly class SettingsController
{
    public function edit(
        SettingsStore $settings,
        OptimizationProviderRegistry $providers,
        OptimizationSettings $optimizationSettings,
    ): View {
        return view('tentapress-media::media.optimizations', [
            'optimizationEnabled' => $optimizationSettings->enabled() ? '1' : '0',
            'optimizationProvider' => $optimizationSettings->provider(),
            'providers' => $providers->all(),
            'providerSettings' => $this->providerSettings(),
            'providerSettingsData' => $this->providerSettingsData($settings),
        ]);
    }

    public function update(
        Request $request,
        SettingsStore $settings,
    ): RedirectResponse {
        $data = $request->validate([
            'optimization_enabled' => ['nullable', 'string'],
            'optimization_provider' => ['nullable', 'string', 'max:80'],
        ]);

        $settings->set('optimization.enabled', $request->has('optimization_enabled') ? '1' : '0', true);
        $settings->set('optimization.provider', trim((string) ($data['optimization_provider'] ?? '')), true);

        $this->persistProviderSettings($request, $settings);

        return to_route('tp.media.optimizations')
            ->with('tp_notice_success', 'Optimization settings saved.');
    }

    /**
     * @return string[]
     */
    private function providerSettings(): array
    {
        $settings = [];

        if (view()->exists('tentapress-media-optimization-cloudflare::settings')) {
            $settings[] = 'tentapress-media-optimization-cloudflare::settings';
        }

        if (view()->exists('tentapress-media-optimization-imgix::settings')) {
            $settings[] = 'tentapress-media-optimization-imgix::settings';
        }

        if (view()->exists('tentapress-media-optimization-imagekit::settings')) {
            $settings[] = 'tentapress-media-optimization-imagekit::settings';
        }

        if (view()->exists('tentapress-media-optimization-bunny::settings')) {
            $settings[] = 'tentapress-media-optimization-bunny::settings';
        }

        return $settings;
    }

    /**
     * @return array<string,string>
     */
    private function providerSettingsData(SettingsStore $settings): array
    {
        $data = [];

        if (class_exists(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        return $data;
    }

    private function persistProviderSettings(Request $request, SettingsStore $settings): void
    {
        if (class_exists(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if (class_exists(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }
    }
}
