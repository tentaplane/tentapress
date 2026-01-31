<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class PageUpdateController
{
    public function __invoke(Request $request, TpPage $page)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'robots' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:2000'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:2000'],
            'twitter_image' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            'page_id' => (int) $page->id,
            'title' => $this->nullIfEmpty($data['title'] ?? null),
            'description' => $this->nullIfEmpty($data['description'] ?? null),
            'canonical_url' => $this->nullIfEmpty($data['canonical_url'] ?? null),
            'robots' => $this->nullIfEmpty($data['robots'] ?? null),
            'og_title' => $this->nullIfEmpty($data['og_title'] ?? null),
            'og_description' => $this->nullIfEmpty($data['og_description'] ?? null),
            'og_image' => $this->nullIfEmpty($data['og_image'] ?? null),
            'twitter_title' => $this->nullIfEmpty($data['twitter_title'] ?? null),
            'twitter_description' => $this->nullIfEmpty($data['twitter_description'] ?? null),
            'twitter_image' => $this->nullIfEmpty($data['twitter_image'] ?? null),
        ];

        $row = TpSeoPage::query()->updateOrCreate(
            ['page_id' => (int) $page->id],
            $payload
        );

        // Optional: if all fields empty, delete row to keep db clean
        if ($this->isEmptyRow($row)) {
            $row->delete();
        }

        return to_route('tp.seo.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'SEO updated.');
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $v = trim((string) ($value ?? ''));

        return $v === '' ? null : $v;
    }

    private function isEmptyRow(TpSeoPage $row): bool
    {
        foreach ([
            'title',
            'description',
            'canonical_url',
            'robots',
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
         ] as $k) {
            $v = $row->{$k};

            if (is_string($v) && trim($v) !== '') {
                return false;
            }

            if ($v !== null && !is_string($v)) {
                return false;
            }
        }

        return true;
    }
}
