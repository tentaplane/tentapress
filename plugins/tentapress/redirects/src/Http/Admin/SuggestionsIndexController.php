<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Redirects\Models\TpRedirectSuggestion;

final class SuggestionsIndexController
{
    public function __invoke(Request $request)
    {
        $state = trim((string) $request->query('state', 'pending'));
        $state = in_array($state, ['pending', 'approved', 'rejected'], true) ? $state : 'pending';

        $suggestions = TpRedirectSuggestion::query()
            ->where('state', $state)
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('tentapress-redirects::redirects.suggestions', [
            'suggestions' => $suggestions,
            'state' => $state,
        ]);
    }
}
