<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Route;
use TentaPress\GlobalContent\Models\TpGlobalContent;

final class GlobalContentReferenceResolver
{
    /**
     * @var array<int,int>
     */
    private array $renderStack = [];

    public function __construct(
        private readonly ViewFactory $views,
    ) {
    }

    public function renderPublishedById(int $id): string
    {
        if ($id <= 0) {
            return '';
        }

        $content = TpGlobalContent::query()->published()->find($id);

        return $content instanceof TpGlobalContent ? $this->renderModel($content) : '';
    }

    public function renderPublishedBySlug(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        $content = TpGlobalContent::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        return $content instanceof TpGlobalContent ? $this->renderModel($content) : '';
    }

    /**
     * @return array<int,array{id:int,value:string,label:string,slug:string,kind:string,status:string,edit_url:?string}>
     */
    public function publishedLibrary(): array
    {
        return TpGlobalContent::query()
            ->published()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'kind', 'status'])
            ->map(static fn (TpGlobalContent $content): array => [
                'id' => (int) $content->id,
                'value' => (string) $content->id,
                'label' => trim((string) $content->title).' ('.trim((string) $content->slug).')',
                'slug' => (string) $content->slug,
                'kind' => (string) $content->kind,
                'status' => (string) $content->status,
                'edit_url' => Route::has('tp.global-content.edit')
                    ? route('tp.global-content.edit', ['globalContent' => $content->id])
                    : null,
            ])
            ->all();
    }

    /**
     * @return array<int,mixed>
     */
    public function detachBlocks(int $id): array
    {
        $content = TpGlobalContent::query()->find($id);
        if (! $content instanceof TpGlobalContent) {
            return [];
        }

        return is_array($content->blocks) ? array_values($content->blocks) : [];
    }

    private function renderModel(TpGlobalContent $content): string
    {
        if (in_array((int) $content->id, $this->renderStack, true)) {
            return '';
        }

        $this->renderStack[] = (int) $content->id;

        try {
            if (app()->bound('tp.blocks.render')) {
                $renderer = resolve('tp.blocks.render');

                if (is_callable($renderer)) {
                    $html = $renderer(is_array($content->blocks) ? $content->blocks : []);
                    if (is_string($html)) {
                        return $html;
                    }
                }
            }

            return $this->views->make('tentapress-global-content::global-content.render', [
                'globalContent' => $content,
                'blocks' => is_array($content->blocks) ? $content->blocks : [],
            ])->render();
        } finally {
            array_pop($this->renderStack);
        }
    }
}
