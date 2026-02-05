<?php

declare(strict_types=1);

namespace TentaPress\AdminShell\Widgets;

use Illuminate\Support\Facades\Route;
use TentaPress\AdminShell\Admin\Widget\AbstractWidget;

final class WelcomeWidget extends AbstractWidget
{
    protected string $id = 'tentapress/admin-shell:welcome';
    protected string $title = 'Welcome';
    protected int $priority = 5;
    protected int $colspan = 2;

    public function render(): string
    {
        $shortcuts = $this->collectShortcuts();

        return $this->view('tentapress-admin::widgets.welcome', [
            'shortcuts' => $shortcuts,
        ]);
    }

    /**
     * Collect available shortcuts based on enabled plugins.
     *
     * @return array<int, array{label: string, route: string, url: string, icon: string|null}>
     */
    private function collectShortcuts(): array
    {
        $shortcuts = [];

        $candidates = [
            [
                'label' => 'New Page',
                'route' => 'tp.pages.create',
                'icon' => 'file-plus',
            ],
            [
                'label' => 'New Post',
                'route' => 'tp.posts.create',
                'icon' => 'file-text',
            ],
            [
                'label' => 'Upload Media',
                'route' => 'tp.media.create',
                'icon' => 'upload',
            ],
            [
                'label' => 'Add User',
                'route' => 'tp.users.create',
                'icon' => 'user-plus',
            ],
            [
                'label' => 'Settings',
                'route' => 'tp.settings.index',
                'icon' => 'settings',
            ],
        ];

        foreach ($candidates as $candidate) {
            if (Route::has($candidate['route'])) {
                $shortcuts[] = [
                    'label' => $candidate['label'],
                    'route' => $candidate['route'],
                    'url' => route($candidate['route']),
                    'icon' => $candidate['icon'] ?? null,
                ];
            }
        }

        return $shortcuts;
    }
}
