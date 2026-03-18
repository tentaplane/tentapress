<?php

declare(strict_types=1);

namespace TentaPress\Marketing\Services;

final readonly class MarketingManager
{
    public function __construct(
        private MarketingProviderRegistry $registry,
        private MarketingSettings $settings,
        private ConsentState $consent,
    ) {
    }

    public function renderPlacement(string $placement): string
    {
        $chunks = [];

        if ($this->canRenderAnalytics()) {
            foreach ($this->registry->all() as $provider) {
                if (! $this->settings->providerEnabled($provider->key())) {
                    continue;
                }

                if (! $provider->isConfigured($this->settings)) {
                    continue;
                }

                $rendered = $provider->render($this->settings);
                $html = trim((string) ($rendered[$placement] ?? ''));

                if ($html !== '') {
                    $chunks[] = $html;
                }
            }
        }

        $custom = trim($this->settings->customScript($placement));

        if ($custom !== '') {
            if (! $this->settings->customScriptRequiresAnalyticsConsent($placement) || $this->canRenderAnalytics()) {
                $chunks[] = $custom;
            }
        }

        return implode("\n", $chunks);
    }

    public function canRenderAnalytics(): bool
    {
        if (! $this->settings->consentEnabled()) {
            return true;
        }

        return $this->consent->analyticsAllowed();
    }

    public function shouldRenderConsent(): bool
    {
        return $this->settings->consentEnabled();
    }

    /**
     * @return array<string,mixed>
     */
    public function consentUiConfig(): array
    {
        return [
            'cookieName' => $this->settings->cookieName(),
            'cookieMaxAgeDays' => $this->settings->cookieMaxAgeDays(),
            'hasDecision' => $this->consent->hasDecision(),
            'analyticsAllowed' => $this->consent->analyticsAllowed(),
            'bannerTitle' => $this->settings->bannerTitle(),
            'bannerBody' => $this->settings->bannerBody(),
            'acceptLabel' => $this->settings->acceptLabel(),
            'rejectLabel' => $this->settings->rejectLabel(),
            'manageLabel' => $this->settings->manageLabel(),
            'privacyButtonLabel' => $this->settings->privacyButtonLabel(),
        ];
    }
}
