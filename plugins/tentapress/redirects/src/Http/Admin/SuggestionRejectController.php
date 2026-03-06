<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\RedirectResponse;
use TentaPress\Redirects\Models\TpRedirectSuggestion;
use TentaPress\Redirects\Services\RedirectSuggestionManager;

final class SuggestionRejectController
{
    public function __invoke(TpRedirectSuggestion $suggestion, RedirectSuggestionManager $manager): RedirectResponse
    {
        if ((string) $suggestion->state !== 'pending') {
            return back()->with('tp_notice_warning', 'Suggestion is not pending.');
        }

        $manager->reject($suggestion);

        return back()->with('tp_notice_success', 'Suggestion rejected.');
    }
}
