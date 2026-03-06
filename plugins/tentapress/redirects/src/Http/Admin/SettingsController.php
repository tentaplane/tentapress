<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Redirects\Services\RedirectPolicy;

final class SettingsController
{
    public function __invoke(Request $request, RedirectPolicy $policy)
    {
        if ($request->isMethod('post')) {
            $policy->setAutoApplySlugRedirects((bool) $request->boolean('auto_apply_slug_redirects'));

            return to_route('tp.redirects.settings')
                ->with('tp_notice_success', 'Redirect policy settings saved.');
        }

        return view('tentapress-redirects::redirects.settings', [
            'autoApplySlugRedirects' => $policy->shouldAutoApplySlugRedirects(),
        ]);
    }
}
