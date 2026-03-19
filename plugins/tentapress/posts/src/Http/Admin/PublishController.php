<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Services\WorkflowManager;

final class PublishController
{
    public function __invoke(TpPost $post)
    {
        if (class_exists(WorkflowManager::class) && app()->bound(WorkflowManager::class)) {
            /** @var TpUser|null $actor */
            $actor = Auth::user();
            abort_unless($actor instanceof TpUser, 403);

            try {
                app()->make(WorkflowManager::class)->publishNow('posts', (int) $post->id, $actor);
            } catch (InvalidArgumentException $exception) {
                return to_route('tp.posts.edit', ['post' => $post->id])
                    ->with('tp_notice_error', $exception->getMessage());
            }

            return to_route('tp.posts.edit', ['post' => $post->id])
                ->with('tp_notice_success', 'Post published.');
        }

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $post->status = 'published';
        $post->published_at = $post->published_at ?: now();
        $post->updated_by = $nowUserId ?: null;
        $post->save();

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post published.');
    }
}
