<?php

declare(strict_types=1);

namespace TentaPress\Boilerplate\Http\Admin;

use Illuminate\Http\RedirectResponse;
use TentaPress\Boilerplate\Http\Requests\UpdateBoilerplateSettingsRequest;
use TentaPress\Boilerplate\Services\BoilerplateSettings;

final class UpdateController
{
    public function __invoke(UpdateBoilerplateSettingsRequest $request, BoilerplateSettings $settings): RedirectResponse
    {
        $settings->save($request->validated());

        return to_route('tp.boilerplate.index')
            ->with('tp_notice_success', 'Boilerplate settings saved.');
    }
}
