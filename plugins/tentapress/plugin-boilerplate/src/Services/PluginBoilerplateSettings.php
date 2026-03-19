<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate\Services;

use TentaPress\Settings\Services\SettingsStore;

final readonly class PluginBoilerplateSettings
{
    public function __construct(
        private SettingsStore $settingsStore,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->settingsStore->get('plugin_boilerplate.enabled', '0') === '1';
    }

    public function endpointPrefix(): string
    {
        return (string) $this->settingsStore->get('plugin_boilerplate.endpoint_prefix', 'plugin-boilerplate');
    }

    public function adminNotice(): string
    {
        return (string) $this->settingsStore->get('plugin_boilerplate.admin_notice', '');
    }

    /**
     * @param array{plugin_enabled?: bool|string|int|null, endpoint_prefix: string, admin_notice?: string|null} $data
     */
    public function save(array $data): void
    {
        $this->settingsStore->set('plugin_boilerplate.enabled', $this->normaliseEnabled($data['plugin_enabled'] ?? null), true);
        $this->settingsStore->set('plugin_boilerplate.endpoint_prefix', trim((string) $data['endpoint_prefix']), true);
        $this->settingsStore->set('plugin_boilerplate.admin_notice', trim((string) ($data['admin_notice'] ?? '')), true);
    }

    private function normaliseEnabled(bool|string|int|null $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0';
    }
}
