<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Services;

use TentaPress\Settings\Services\SettingsStore;

final readonly class MarketingSettings
{
    public function __construct(private SettingsStore $settings)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings->get($key, $default);
    }

    public function set(string $key, mixed $value, bool $autoload = true): void
    {
        $this->settings->set($key, $value, $autoload);
    }

    public function providerEnabled(string $providerKey): bool
    {
        return $this->get('marketing.providers.'.$providerKey.'.enabled', '0') === '1';
    }

    public function providerValue(string $providerKey, string $fieldKey, string $default = ''): string
    {
        return trim((string) $this->get('marketing.providers.'.$providerKey.'.'.$fieldKey, $default));
    }

    public function customScript(string $placement): string
    {
        return (string) $this->get('marketing.custom_scripts.'.$placement.'.code', '');
    }

    public function customScriptRequiresAnalyticsConsent(string $placement): bool
    {
        return $this->get('marketing.custom_scripts.'.$placement.'.analytics_consent', '0') === '1';
    }

    public function consentEnabled(): bool
    {
        return $this->get('marketing.consent.enabled', '1') === '1';
    }

    public function cookieName(): string
    {
        $value = trim((string) $this->get('marketing.consent.cookie_name', 'tp_marketing_consent'));

        return $value !== '' ? $value : 'tp_marketing_consent';
    }

    public function cookieMaxAgeDays(): int
    {
        $value = (int) $this->get('marketing.consent.cookie_max_age_days', '180');

        return max(1, min(3650, $value > 0 ? $value : 180));
    }

    public function bannerTitle(): string
    {
        return (string) $this->get('marketing.consent.banner_title', 'Analytics preferences');
    }

    public function bannerBody(): string
    {
        return (string) $this->get(
            'marketing.consent.banner_body',
            'We use analytics to understand site performance and improve the experience.'
        );
    }

    public function acceptLabel(): string
    {
        return (string) $this->get('marketing.consent.accept_label', 'Accept analytics');
    }

    public function rejectLabel(): string
    {
        return (string) $this->get('marketing.consent.reject_label', 'Reject analytics');
    }

    public function manageLabel(): string
    {
        return (string) $this->get('marketing.consent.manage_label', 'Manage preferences');
    }

    public function privacyButtonLabel(): string
    {
        return (string) $this->get('marketing.consent.privacy_button_label', 'Privacy');
    }
}
