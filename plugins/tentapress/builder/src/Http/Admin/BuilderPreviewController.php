<?php

declare(strict_types=1);

namespace TentaPress\Builder\Http\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use TentaPress\Builder\Support\PreviewSnapshotStore;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Pages\Services\PageRenderer;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Posts\Services\PostRenderer;

final readonly class BuilderPreviewController
{
    public function __construct(
        private PreviewSnapshotStore $snapshots,
        private PageRenderer $pages,
        private PostRenderer $posts,
    ) {
    }

    public function __invoke(string $token): Response
    {
        $userId = (int) (Auth::user()?->id ?? 0);
        abort_if($userId <= 0, 403);

        $payload = $this->snapshots->get($token, $userId);
        abort_if(! is_array($payload), 404);

        $resource = (string) ($payload['resource'] ?? 'pages');

        if ($resource === 'posts') {
            $post = new TpPost([
                'title' => (string) ($payload['title'] ?? 'Preview'),
                'slug' => (string) ($payload['slug'] ?? ''),
                'status' => 'draft',
                'layout' => (string) ($payload['layout'] ?? 'default'),
                'editor_driver' => 'builder',
                'blocks' => is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [],
                'content' => null,
                'author_id' => $userId,
            ]);

            return $this->posts->render($post);
        }

        $page = new TpPage([
            'title' => (string) ($payload['title'] ?? 'Preview'),
            'slug' => (string) ($payload['slug'] ?? ''),
            'status' => 'draft',
            'layout' => (string) ($payload['layout'] ?? 'default'),
            'editor_driver' => 'builder',
            'blocks' => is_array($payload['blocks'] ?? null) ? $payload['blocks'] : [],
            'content' => null,
        ]);

        return $this->pages->render($page);
    }
}
