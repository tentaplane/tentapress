<?php

declare(strict_types=1);

namespace TentaPress\Themes\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\System\Theme\ThemeManager;

final class ActivateController
{
    public function __invoke(Request $request, ThemeManager $manager)
    {
        $data = $request->validate([
            'theme_id' => ['required', 'string'],
        ]);

        $manager->activate((string) $data['theme_id']);

        return to_route('tp.themes.index')
            ->with('tp_notice_success', 'Theme activated.');
    }
}
