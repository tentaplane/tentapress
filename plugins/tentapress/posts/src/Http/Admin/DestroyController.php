<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use TentaPress\Posts\Models\TpPost;

final class DestroyController
{
    public function __invoke(TpPost $post)
    {
        $post->delete();

        return to_route('tp.posts.index')
            ->with('tp_notice_success', 'Post deleted.');
    }
}
