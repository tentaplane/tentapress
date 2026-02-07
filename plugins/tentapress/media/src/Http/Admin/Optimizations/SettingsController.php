<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Optimizations;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\Media\Optimization\OptimizationSettings;
use TentaPress\Media\Support\MediaFeatureAvailability;
use TentaPress\Settings\Services\SettingsStore;

final readonly class SettingsController
{
    public function edit(
        SettingsStore $settings,
        OptimizationProviderRegistry $providers,
        OptimizationSettings $optimizationSettings,
        MediaFeatureAvailability $features,
    ): View|RedirectResponse {
        if (! $features->hasOptimizationProviders()) {
            return to_route('tp.media.index')
                ->with('tp_notice_warning', 'No optimization provider plugins are enabled.');
        }

        $enabledProviders = $providers->enabled();
        $enabledProviderKeys = array_map(
            static fn ($provider): string => $provider->key(),
            $enabledProviders
        );
        $selectedProvider = $optimizationSettings->provider();
        if (! in_array($selectedProvider, $enabledProviderKeys, true)) {
            $selectedProvider = $enabledProviderKeys[0] ?? '';
        }

        return view('tentapress-media::media.optimizations', [
            'optimizationEnabled' => $optimizationSettings->enabled() ? '1' : '0',
            'optimizationProvider' => $selectedProvider,
            'providers' => $enabledProviders,
            'providerSettings' => $this->providerSettings($features),
            'providerSettingsData' => $this->providerSettingsData($settings, $features),
        ]);
    }

    public function update(
        Request $request,
        SettingsStore $settings,
        OptimizationProviderRegistry $providers,
        MediaFeatureAvailability $features,
    ): RedirectResponse {
        if (! $features->hasOptimizationProviders()) {
            return to_route('tp.media.index')
                ->with('tp_notice_warning', 'No optimization provider plugins are enabled.');
        }

        $enabledProviderKeys = array_map(
            static fn ($provider): string => $provider->key(),
            $providers->enabled()
        );

        $data = $request->validate([
            'optimization_enabled' => ['nullable', 'string'],
            'optimization_provider' => ['nullable', 'string', 'max:80'],
        ]);

        $provider = trim((string) ($data['optimization_provider'] ?? ''));
        if ($provider !== '' && ! in_array($provider, $enabledProviderKeys, true)) {
            return to_route('tp.media.optimizations')
                ->with('tp_notice_error', 'Selected optimization service is not enabled.');
        }

        $settings->set('optimization.enabled', $request->has('optimization_enabled') ? '1' : '0', true);
        $settings->set('optimization.provider', $provider, true);

        $this->persistProviderSettings($request, $settings, $features);

        return to_route('tp.media.optimizations')
            ->with('tp_notice_success', 'Optimization settings saved.');
    }

    /**
     * @return string[]
     */
    private function providerSettings(MediaFeatureAvailability $features): array
    {
        $settings = [];

        if ($features->isEnabled('tentapress/media-optimization-cloudflare') && view()->exists('tentapress-media-optimization-cloudflare::settings')) {
            $settings[] = 'tentapress-media-optimization-cloudflare::settings';
        }

        if ($features->isEnabled('tentapress/media-optimization-imgix') && view()->exists('tentapress-media-optimization-imgix::settings')) {
            $settings[] = 'tentapress-media-optimization-imgix::settings';
        }

        if ($features->isEnabled('tentapress/media-optimization-imagekit') && view()->exists('tentapress-media-optimization-imagekit::settings')) {
            $settings[] = 'tentapress-media-optimization-imagekit::settings';
        }

        if ($features->isEnabled('tentapress/media-optimization-bunny') && view()->exists('tentapress-media-optimization-bunny::settings')) {
            $settings[] = 'tentapress-media-optimization-bunny::settings';
        }

        return $settings;
    }

    /**
     * @return array<string,string>
     */
    private function providerSettingsData(SettingsStore $settings, MediaFeatureAvailability $features): array
    {
        $data = [];

        if ($features->isEnabled('tentapress/media-optimization-cloudflare') && class_exists(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-imgix') && class_exists(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-imagekit') && class_exists(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-bunny') && class_exists(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'defaults')) {
                $data = array_merge($data, $controller->defaults($settings));
            }
        }

        return $data;
    }

    private function persistProviderSettings(Request $request, SettingsStore $settings, MediaFeatureAvailability $features): void
    {
        if ($features->isEnabled('tentapress/media-optimization-cloudflare') && class_exists(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationCloudflare\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-imgix') && class_exists(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImgix\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-imagekit') && class_exists(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationImageKit\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }

        if ($features->isEnabled('tentapress/media-optimization-bunny') && class_exists(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class)) {
            $controller = resolve(\TentaPress\MediaOptimizationBunny\Http\Admin\SettingsController::class);
            if (method_exists($controller, 'validate') && method_exists($controller, 'persist')) {
                $data = $controller->validate($request);
                $controller->persist($request, $settings, $data);
            }
        }
    }
}
