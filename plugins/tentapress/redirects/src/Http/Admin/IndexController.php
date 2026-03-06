<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Redirects\Models\TpRedirect;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $statusCode = (int) $request->query('status_code', 0);
        $enabled = $request->query('enabled');

        $redirects = TpRedirect::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->whereLike('source_path', "%{$search}%")
                        ->orWhereLike('target_path', "%{$search}%");
                });
            })
            ->when(in_array($statusCode, [301, 302], true), function ($query) use ($statusCode): void {
                $query->where('status_code', $statusCode);
            })
            ->when($enabled === '1' || $enabled === '0', function ($query) use ($enabled): void {
                $query->where('is_enabled', $enabled === '1');
            })
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('tentapress-redirects::redirects.index', [
            'redirects' => $redirects,
            'search' => $search,
            'statusCode' => $statusCode,
            'enabled' => $enabled,
        ]);
    }
}
