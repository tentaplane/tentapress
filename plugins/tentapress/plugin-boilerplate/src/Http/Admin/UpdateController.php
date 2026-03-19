<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate\Http\Admin;

use Illuminate\Http\RedirectResponse;
use TentaPress\PluginBoilerplate\Http\Requests\UpdatePluginBoilerplateSettingsRequest;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateSettings;

final class UpdateController
{
    public function __invoke(UpdatePluginBoilerplateSettingsRequest $request, PluginBoilerplateSettings $settings): RedirectResponse
    {
        $settings->save($request->validated());

        return to_route('tp.plugin-boilerplate.index')
            ->with('tp_notice_success', 'Plugin boilerplate settings saved.');
    }
}
