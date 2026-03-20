<?php

declare(strict_types=1);

namespace TentaPress\Pages\ContentReference;

use TentaPress\Pages\Models\TpPage;
use TentaPress\System\ContentReference\ContentReference;
use TentaPress\System\ContentReference\ContentReferenceSource;

final class PageContentReferenceSource implements ContentReferenceSource
{
    public function key(): string
    {
        return 'pages';
    }

    public function find(string $id): ?ContentReference
    {
        $page = TpPage::query()->find((int) $id);

        if (! $page instanceof TpPage) {
            return null;
        }

        return new ContentReference(
            source: $this->key(),
            id: (string) $page->getKey(),
            title: $this->titleFor($page),
            typeLabel: 'Page',
        );
    }

    public function options(array $constraints = []): array
    {
        return TpPage::query()
            ->orderBy('title')
            ->get()
            ->map(fn (TpPage $page): ContentReference => new ContentReference(
                source: $this->key(),
                id: (string) $page->getKey(),
                title: $this->titleFor($page),
                typeLabel: 'Page',
            ))
            ->all();
    }

    private function titleFor(TpPage $page): string
    {
        $title = trim((string) $page->title);

        return $title !== '' ? $title : 'Page #'.$page->getKey();
    }
}
