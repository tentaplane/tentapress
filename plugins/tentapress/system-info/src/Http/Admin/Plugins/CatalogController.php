<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Admin\Plugins;

use Illuminate\Contracts\View\View;
use TentaPress\SystemInfo\Services\PluginCatalogService;

final class CatalogController
{
    public function __invoke(PluginCatalogService $catalog): View
    {
        $result = $catalog->catalog();

        return view('tentapress-system-info::plugins.catalog', [
            'entries' => $result['entries'],
            'warning' => $result['warning'],
            'canManagePlugins' => $this->canManagePlugins(),
            'canInstallPlugins' => $this->canInstallPlugins(),
            'installUrl' => route('tp.plugins.install'),
            'statusUrlTemplate' => route('tp.plugins.install-attempts.show', ['installId' => '__ID__']),
            'csrfToken' => csrf_token(),
        ]);
    }

    private function canManagePlugins(): bool
    {
        $user = auth()->user();

        if (! is_object($user)) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return method_exists($user, 'hasCapability') && $user->hasCapability('manage_plugins');
    }

    private function canInstallPlugins(): bool
    {
        $user = auth()->user();

        return is_object($user) && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }
}
