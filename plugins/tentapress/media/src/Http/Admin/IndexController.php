<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Media\Support\MediaFeatureAvailability;

final class IndexController
{
    public function __invoke(
        Request $request,
        MediaFeatureAvailability $features,
    ): View {
        $search = trim((string) $request->query('s', ''));
        $preferredView = (string) $request->query('view', '');
        if ($preferredView === '') {
            $preferredView = (string) $request->cookie('tp_media_view', '');
        }

        $view = $preferredView !== '' ? $preferredView : 'list';
        $view = in_array($view, ['list', 'grid'], true) ? $view : 'list';

        $query = TpMedia::query()->latest('created_at');

        if ($search !== '') {
            $query->where(function ($qq) use ($search): void {
                $qq->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('original_name', '%'.$search.'%');
            });
        }

        $media = $query->paginate(24)->withQueryString();

        return view('tentapress-media::media.index', [
            'media' => $media,
            'search' => $search,
            'view' => $view,
            'hasStockSources' => $features->hasStockSources(),
            'hasOptimizationProviders' => $features->hasOptimizationProviders(),
        ]);
    }
}
