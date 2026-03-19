<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Services\WorkflowManager;

final class UnpublishController
{
    public function __invoke(TpPost $post)
    {
        if (class_exists(WorkflowManager::class) && app()->bound(WorkflowManager::class)) {
            /** @var TpUser|null $actor */
            $actor = Auth::user();
            abort_unless($actor instanceof TpUser, 403);

            app()->make(WorkflowManager::class)->unpublish('posts', (int) $post->id, $actor);

            return to_route('tp.posts.edit', ['post' => $post->id])
                ->with('tp_notice_success', 'Post reverted to draft.');
        }

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $post->status = 'draft';
        $post->updated_by = $nowUserId ?: null;
        $post->save();

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post reverted to draft.');
    }
}
