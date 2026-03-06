<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use TentaPress\Redirects\Models\TpRedirectSuggestion;
use TentaPress\Redirects\Services\RedirectSuggestionManager;

final class SuggestionApproveController
{
    public function __invoke(TpRedirectSuggestion $suggestion, RedirectSuggestionManager $manager): RedirectResponse
    {
        if ((string) $suggestion->state !== 'pending') {
            return back()->with('tp_notice_warning', 'Suggestion is not pending.');
        }

        try {
            $manager->approve($suggestion);
        } catch (InvalidArgumentException $exception) {
            return back()->with('tp_notice_error', $exception->getMessage());
        }

        return back()->with('tp_notice_success', 'Suggestion approved and redirect created.');
    }
}
