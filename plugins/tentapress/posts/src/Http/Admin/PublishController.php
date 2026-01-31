<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Posts\Models\TpPost;

final class PublishController
{
    public function __invoke(TpPost $post)
    {
        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $post->status = 'published';
        $post->published_at = $post->published_at ?: now();
        $post->updated_by = $nowUserId ?: null;
        $post->save();

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post published.');
    }
}
