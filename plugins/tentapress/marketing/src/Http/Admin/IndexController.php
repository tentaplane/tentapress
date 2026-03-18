<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Marketing\Contracts\MarketingProvider;
use TentaPress\Marketing\Services\MarketingProviderRegistry;
use TentaPress\Marketing\Services\MarketingSettings;

final class IndexController
{
    public function __invoke(Request $request, MarketingProviderRegistry $registry, MarketingSettings $settings)
    {
        if ($request->isMethod('post')) {
            $data = $this->validateRequest($request, $registry);
            $this->persist($request, $registry, $settings, $data);

            return to_route('tp.marketing.index')
                ->with('tp_notice_success', 'Marketing settings saved.');
        }

        return view('tentapress-marketing::index', [
            'providers' => array_map(
                fn (MarketingProvider $provider): array => $this->providerViewData($provider, $settings),
                array_values($registry->all())
            ),
            'slots' => [
                'head' => [
                    'label' => 'Head scripts',
                    'help' => 'Rendered inside the head partial.',
                    'code' => $settings->customScript('head'),
                    'analytics_consent' => $settings->customScriptRequiresAnalyticsConsent('head'),
                ],
                'body-open' => [
                    'label' => 'Body open scripts',
                    'help' => 'Rendered immediately after the opening body tag.',
                    'code' => $settings->customScript('body-open'),
                    'analytics_consent' => $settings->customScriptRequiresAnalyticsConsent('body-open'),
                ],
                'body-close' => [
                    'label' => 'Body close scripts',
                    'help' => 'Rendered before the closing body tag.',
                    'code' => $settings->customScript('body-close'),
                    'analytics_consent' => $settings->customScriptRequiresAnalyticsConsent('body-close'),
                ],
            ],
            'consent' => [
                'enabled' => $settings->consentEnabled(),
                'cookie_name' => $settings->cookieName(),
                'cookie_max_age_days' => $settings->cookieMaxAgeDays(),
                'banner_title' => $settings->bannerTitle(),
                'banner_body' => $settings->bannerBody(),
                'accept_label' => $settings->acceptLabel(),
                'reject_label' => $settings->rejectLabel(),
                'manage_label' => $settings->manageLabel(),
                'privacy_button_label' => $settings->privacyButtonLabel(),
            ],
        ]);
    }

    private function validateRequest(Request $request, MarketingProviderRegistry $registry): array
    {
        $rules = [
            'consent_cookie_name' => ['required', 'string', 'max:120'],
            'consent_cookie_max_age_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'consent_banner_title' => ['required', 'string', 'max:255'],
            'consent_banner_body' => ['required', 'string', 'max:1000'],
            'consent_accept_label' => ['required', 'string', 'max:100'],
            'consent_reject_label' => ['required', 'string', 'max:100'],
            'consent_manage_label' => ['required', 'string', 'max:100'],
            'consent_privacy_button_label' => ['required', 'string', 'max:100'],
            'custom_script_head' => ['nullable', 'string', 'max:20000'],
            'custom_script_body_open' => ['nullable', 'string', 'max:20000'],
            'custom_script_body_close' => ['nullable', 'string', 'max:20000'],
        ];

        foreach ($registry->all() as $provider) {
            foreach ($provider->fields() as $field) {
                $rules['provider_'.$provider->key().'_'.$field['key']] = ['nullable', 'string', 'max:'.$field['max']];
            }
        }

        return $request->validate($rules);
    }

    private function persist(
        Request $request,
        MarketingProviderRegistry $registry,
        MarketingSettings $settings,
        array $data
    ): void {
        $settings->set('marketing.consent.enabled', $request->has('consent_enabled') ? '1' : '0', true);
        $settings->set('marketing.consent.cookie_name', trim((string) $data['consent_cookie_name']), true);
        $settings->set('marketing.consent.cookie_max_age_days', (string) $data['consent_cookie_max_age_days'], true);
        $settings->set('marketing.consent.banner_title', trim((string) $data['consent_banner_title']), true);
        $settings->set('marketing.consent.banner_body', trim((string) $data['consent_banner_body']), true);
        $settings->set('marketing.consent.accept_label', trim((string) $data['consent_accept_label']), true);
        $settings->set('marketing.consent.reject_label', trim((string) $data['consent_reject_label']), true);
        $settings->set('marketing.consent.manage_label', trim((string) $data['consent_manage_label']), true);
        $settings->set('marketing.consent.privacy_button_label', trim((string) $data['consent_privacy_button_label']), true);

        $settings->set('marketing.custom_scripts.head.code', (string) ($data['custom_script_head'] ?? ''), true);
        $settings->set(
            'marketing.custom_scripts.head.analytics_consent',
            $request->has('custom_script_head_analytics_consent') ? '1' : '0',
            true
        );
        $settings->set('marketing.custom_scripts.body-open.code', (string) ($data['custom_script_body_open'] ?? ''), true);
        $settings->set(
            'marketing.custom_scripts.body-open.analytics_consent',
            $request->has('custom_script_body_open_analytics_consent') ? '1' : '0',
            true
        );
        $settings->set('marketing.custom_scripts.body-close.code', (string) ($data['custom_script_body_close'] ?? ''), true);
        $settings->set(
            'marketing.custom_scripts.body-close.analytics_consent',
            $request->has('custom_script_body_close_analytics_consent') ? '1' : '0',
            true
        );

        foreach ($registry->all() as $provider) {
            $providerKey = $provider->key();
            $settings->set(
                'marketing.providers.'.$providerKey.'.enabled',
                $request->has('provider_'.$providerKey.'_enabled') ? '1' : '0',
                true
            );

            foreach ($provider->fields() as $field) {
                $key = 'provider_'.$providerKey.'_'.$field['key'];
                $settings->set('marketing.providers.'.$providerKey.'.'.$field['key'], trim((string) ($data[$key] ?? '')), true);
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function providerViewData(MarketingProvider $provider, MarketingSettings $settings): array
    {
        return [
            'key' => $provider->key(),
            'label' => $provider->label(),
            'description' => $provider->description(),
            'enabled' => $settings->providerEnabled($provider->key()),
            'configured' => $provider->isConfigured($settings),
            'fields' => array_map(function (array $field) use ($provider, $settings): array {
                $field['value'] = $settings->providerValue($provider->key(), $field['key'], $field['default']);

                return $field;
            }, $provider->fields()),
        ];
    }
}
