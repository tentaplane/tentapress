<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Services;

use TentaPress\Settings\Services\SettingsStore;

final readonly class RedirectPolicy
{
    public function __construct(
        private ?SettingsStore $settings,
    ) {
    }

    public function shouldAutoApplySlugRedirects(): bool
    {
        if (! $this->settings instanceof SettingsStore) {
            return true;
        }

        $raw = (string) $this->settings->get('redirects.auto_apply_slug_redirects', '1');

        return in_array($raw, ['1', 'true', 'yes'], true);
    }

    public function setAutoApplySlugRedirects(bool $value): void
    {
        if (! $this->settings instanceof SettingsStore) {
            return;
        }

        $this->settings->set('redirects.auto_apply_slug_redirects', $value ? '1' : '0', true);
    }
}
