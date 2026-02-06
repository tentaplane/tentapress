<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Admin\Stock;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockQuery;
use TentaPress\Media\Stock\StockSettings;

final class IndexController
{
    public function __invoke(Request $request, StockManager $manager, StockSettings $settings): View
    {
        $query = trim((string) $request->query('q', ''));
        $sourceKey = (string) $request->query('source', '');
        $mediaType = (string) $request->query('media_type', '');
        $page = max(1, (int) $request->query('page', 1));

        $enabledSources = $manager->enabled();
        $defaultSource = $enabledSources[0] ?? null;

        $source = $sourceKey !== '' ? $manager->get($sourceKey) : null;
        if ($source === null || ! $source->isEnabled()) {
            $source = $defaultSource;
            $sourceKey = $source?->key() ?? '';
        }

        $results = null;
        if ($source !== null && $query !== '') {
            $results = $source->search(new StockQuery(
                query: $query,
                mediaType: $mediaType !== '' ? $mediaType : null,
                page: $page,
            ));
        }

        return view('tentapress-media::media.stock', [
            'query' => $query,
            'sourceKey' => $sourceKey,
            'mediaType' => $mediaType,
            'sources' => $enabledSources,
            'results' => $results,
            'attributionReminder' => $settings->attributionReminderEnabled(),
        ]);
    }
}
