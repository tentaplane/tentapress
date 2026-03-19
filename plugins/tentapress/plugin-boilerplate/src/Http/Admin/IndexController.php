<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateSettings;

final class IndexController
{
    public function __invoke(PluginBoilerplateSettings $settings): View
    {
        return view('tentapress-plugin-boilerplate::index', [
            'pluginEnabled' => $settings->isEnabled(),
            'endpointPrefix' => $settings->endpointPrefix(),
            'adminNotice' => $settings->adminNotice(),
        ]);
    }
}
