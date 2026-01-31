<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Support\Facades\Auth;
use TentaPress\Posts\Models\TpPost;

final class UnpublishController
{
    public function __invoke(TpPost $post)
    {
        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $post->status = 'draft';
        $post->updated_by = $nowUserId ?: null;
        $post->save();

        return to_route('tp.posts.edit', ['post' => $post->id])
            ->with('tp_notice_success', 'Post reverted to draft.');
    }
}
