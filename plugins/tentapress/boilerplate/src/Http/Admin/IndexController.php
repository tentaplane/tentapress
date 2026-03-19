<?php

declare(strict_types=1);

namespace TentaPress\Boilerplate\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\Boilerplate\Services\BoilerplateSettings;

final class IndexController
{
    public function __invoke(BoilerplateSettings $settings): View
    {
        return view('tentapress-boilerplate::index', [
            'pluginEnabled' => $settings->isEnabled(),
            'endpointPrefix' => $settings->endpointPrefix(),
            'adminNotice' => $settings->adminNotice(),
        ]);
    }
}
