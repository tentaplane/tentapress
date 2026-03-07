<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Pages\Support\BlocksNormalizer;
use TentaPress\GlobalContent\Http\Requests\UpdateGlobalContentRequest;
use TentaPress\GlobalContent\Models\TpGlobalContent;
use TentaPress\GlobalContent\Services\GlobalContentCycleValidator;
use TentaPress\GlobalContent\Services\GlobalContentSlugger;

final readonly class UpdateController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
        private GlobalContentCycleValidator $cycleValidator,
        private GlobalContentSlugger $slugger,
    ) {
    }

    public function __invoke(UpdateGlobalContentRequest $request, TpGlobalContent $globalContent)
    {
        $data = $request->validated();
        $blocksRaw = json_decode((string) ($data['blocks_json'] ?? ''), true);
        $blocks = $this->normalizer->normalize($blocksRaw);
        $this->cycleValidator->assertValid($blocks, (int) $globalContent->id);

        $title = (string) $data['title'];
        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? $this->slugger->unique($slug, (int) $globalContent->id) : $this->slugger->unique($title, (int) $globalContent->id);

        $userId = (int) (Auth::user()?->id ?? 0);

        $globalContent->fill([
            'title' => $title,
            'slug' => $slug,
            'kind' => (string) $data['kind'],
            'status' => (string) $data['status'],
            'editor_driver' => (string) $data['editor_driver'],
            'blocks' => $blocks,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'updated_by' => $userId > 0 ? $userId : null,
        ]);
        $globalContent->save();

        if ($request->string('return_to')->toString() === 'editor' || (string) $globalContent->editor_driver === 'builder' && $request->boolean('editor_mode')) {
            return to_route('tp.global-content.editor', ['globalContent' => $globalContent->id])
                ->with('tp_notice_success', 'Global content updated.');
        }

        return to_route('tp.global-content.edit', ['globalContent' => $globalContent->id])
            ->with('tp_notice_success', 'Global content updated.');
    }
}
