<?php

declare(strict_types=1);

namespace TentaPress\Import\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProgressController
{
    public function __invoke(Request $request, string $run): View|RedirectResponse
    {
        $payload = $request->session()->get("tp_import.progress.{$run}");

        if (!is_array($payload)) {
            return to_route('tp.import.index')
                ->with('tp_notice_error', 'Import progress session not found. Please start import again.');
        }

        return view('tentapress-import::progress', [
            'run' => $run,
            'payload' => $payload,
        ]);
    }
}
