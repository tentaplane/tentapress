<?php

declare(strict_types=1);

namespace TentaPress\Seo\Http\Admin;

use Illuminate\Http\Request;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Services\SeoPayload;

final class PageUpdateController
{
    public function __construct(private readonly SeoPayload $payload)
    {
    }

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
            'title' => $this->payload->nullIfEmpty($data['title'] ?? null),
            'description' => $this->payload->nullIfEmpty($data['description'] ?? null),
            'canonical_url' => $this->payload->nullIfEmpty($data['canonical_url'] ?? null),
            'robots' => $this->payload->nullIfEmpty($data['robots'] ?? null),
            'og_title' => $this->payload->nullIfEmpty($data['og_title'] ?? null),
            'og_description' => $this->payload->nullIfEmpty($data['og_description'] ?? null),
            'og_image' => $this->payload->nullIfEmpty($data['og_image'] ?? null),
            'twitter_title' => $this->payload->nullIfEmpty($data['twitter_title'] ?? null),
            'twitter_description' => $this->payload->nullIfEmpty($data['twitter_description'] ?? null),
            'twitter_image' => $this->payload->nullIfEmpty($data['twitter_image'] ?? null),
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

    private function isEmptyRow(TpSeoPage $row): bool
    {
        $keys = $this->payloadKeys();
        $payload = [];

        foreach ($keys as $key) {
            $payload[$key] = $row->{$key};
        }

        return $this->payload->isEmpty($payload, $keys);
    }

    /**
     * @return array<int,string>
     */
    private function payloadKeys(): array
    {
        return [
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
        ];
    }
}
