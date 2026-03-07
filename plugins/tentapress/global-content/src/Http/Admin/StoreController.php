<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Pages\Support\BlocksNormalizer;
use TentaPress\GlobalContent\Http\Requests\StoreGlobalContentRequest;
use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Services\GlobalContentCycleValidator;
use TentaPress\GlobalContent\Services\GlobalContentSlugger;

final readonly class StoreController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
        private GlobalContentCycleValidator $cycleValidator,
        private GlobalContentSlugger $slugger,
    ) {
    }

    public function __invoke(StoreGlobalContentRequest $request)
    {
        $data = $request->validated();
        $blocksRaw = json_decode((string) ($data['blocks_json'] ?? ''), true);
        $blocks = $this->normalizer->normalize($blocksRaw);
        $this->cycleValidator->assertValid($blocks);

        $title = (string) $data['title'];
        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? $this->slugger->unique($slug) : $this->slugger->unique($title);

        $userId = (int) (Auth::user()?->id ?? 0);

        $content = TpGlobalContent::query()->create([
            'title' => $title,
            'slug' => $slug,
            'kind' => (string) $data['kind'],
            'status' => (string) $data['status'],
            'editor_driver' => (string) $data['editor_driver'],
            'blocks' => $blocks,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'created_by' => $userId > 0 ? $userId : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        if ((string) $content->editor_driver === 'builder') {
            return to_route('tp.global-content.editor', ['globalContent' => $content->id])
                ->with('tp_notice_success', 'Global content created.');
        }

        return to_route('tp.global-content.edit', ['globalContent' => $content->id])
            ->with('tp_notice_success', 'Global content created.');
    }
}
