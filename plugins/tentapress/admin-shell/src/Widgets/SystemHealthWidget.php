<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Widgets;

use TentaPress\AdminShell\Admin\Widget\AbstractWidget;

final class SystemHealthWidget extends AbstractWidget
{
    protected string $id = 'tentapress/admin-shell:system-health';
    protected string $title = 'System';
    protected int $priority = 5;
    protected int $colspan = 1;

    public function render(): string
    {
        $info = $this->collectInfo();

        return $this->view('tentapress-admin::widgets.system-health', [
            'info' => $info,
        ]);
    }

    /**
     * Collect system information defensively.
     *
     * @return array<int, array{label: string, value: string}>
     */
    private function collectInfo(): array
    {
        $info = [];

        // PHP Version
        $info[] = [
            'label' => 'PHP',
            'value' => PHP_VERSION,
        ];

        // Laravel Version
        try {
            $info[] = [
                'label' => 'Laravel',
                'value' => app()->version(),
            ];
        } catch (\Throwable) {
            // Skip
        }

        // Environment
        try {
            $info[] = [
                'label' => 'Environment',
                'value' => app()->environment(),
            ];
        } catch (\Throwable) {
            // Skip
        }

        // Debug Mode
        try {
            $info[] = [
                'label' => 'Debug',
                'value' => config('app.debug') ? 'Enabled' : 'Disabled',
            ];
        } catch (\Throwable) {
            // Skip
        }

        return $info;
    }
}
