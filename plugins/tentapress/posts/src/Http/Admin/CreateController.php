<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use TentaPress\Blocks\Registry\BlockRegistry;
use TentaPress\Media\Models\TpMedia;
use TentaPress\Posts\Models\TpPost;
use TentaPress\System\Theme\ThemeManager;
use TentaPress\Users\Models\TpUser;

final class CreateController
{
    public function __invoke(ThemeManager $themes)
    {
        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $post = new TpPost([
            'title' => '',
            'slug' => '',
            'status' => 'draft',
            'layout' => null,
            'blocks' => [],
            'content' => ['type' => 'page', 'content' => []],
            'author_id' => $nowUserId ?: null,
        ]);

        $pageDocJson = json_encode($post->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($pageDocJson === false) {
            $pageDocJson = '{"type":"page","content":[]}';
        }

        return view('tentapress-posts::posts.form', [
            'mode' => 'create',
            'post' => $post,
            'blocksJson' => '[]',
            'pageDocJson' => $pageDocJson,
            'themeLayouts' => $themes->activeLayouts(),
            'hasTheme' => $themes->hasActiveTheme(),
            'blockDefinitions' => $this->blockDefinitions(),
            'mediaOptions' => $this->mediaOptions(),
            'authors' => $this->authors(),
            'authorId' => $nowUserId ?: null,
        ]);
    }

    /**
     * @return array<int,array{type:string,name:string,description:string,example:array}>
     */
    private function blockDefinitions(): array
    {
        $registryClass = BlockRegistry::class;

        if (! class_exists($registryClass)) {
            return [];
        }

        if (! app()->bound($registryClass)) {
            return [];
        }

        $registry = resolve($registryClass);

        if (! is_object($registry) || ! method_exists($registry, 'all')) {
            return [];
        }

        $defs = $registry->all();

        $out = [];

        foreach ($defs as $def) {
            $out[] = [
                'type' => (string) ($def->type ?? ''),
                'name' => (string) ($def->name ?? ''),
                'description' => (string) ($def->description ?? ''),
                'version' => (int) ($def->version ?? 1),
                'fields' => is_array($def->fields ?? null) ? $def->fields : [],
                'variants' => is_array($def->variants ?? null) ? $def->variants : [],
                'default_variant' => isset($def->defaultVariant) ? (string) $def->defaultVariant : null,
                'defaults' => is_array($def->defaults ?? null) ? $def->defaults : [],
                'example' => is_array($def->example ?? null) ? $def->example : [],
                'view' => isset($def->view) ? (string) $def->view : null,
            ];
        }

        return array_values(array_filter($out, static fn ($d) => ($d['type'] ?? '') !== ''));
    }

    /**
     * @return array<int,TpUser>
     */
    private function authors(): array
    {
        if (! class_exists(TpUser::class)) {
            return [];
        }

        if (! Schema::hasTable('tp_users')) {
            return [];
        }

        return TpUser::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->all();
    }

    /**
     * @return array<int,array{value:string,label:string,original_name:string,mime_type:string,is_image:bool}>
     */
    private function mediaOptions(): array
    {
        if (! class_exists(TpMedia::class)) {
            return [];
        }

        if (! Schema::hasTable('tp_media')) {
            return [];
        }

        $items = TpMedia::query()
            ->latest('created_at')
            ->limit(200)
            ->get(['id', 'title', 'original_name', 'path', 'mime_type', 'disk']);

        $options = [];

        foreach ($items as $item) {
            $disk = (string) ($item->disk ?? 'public');
            $path = trim((string) ($item->path ?? ''));
            if ($disk !== 'public' || $path === '') {
                continue;
            }

            $url = '/storage/'.ltrim($path, '/');
            $title = trim((string) ($item->title ?? ''));
            $original = trim((string) ($item->original_name ?? ''));
            $label = $title !== '' ? $title : ($original !== '' ? $original : 'Media #'.$item->id);

            $mime = (string) ($item->mime_type ?? '');
            $isImage = $mime !== '' && str_starts_with($mime, 'image/');

            $options[] = [
                'value' => $url,
                'label' => $label,
                'original_name' => $original,
                'mime_type' => $mime,
                'is_image' => $isImage,
            ];
        }

        return $options;
    }
}
