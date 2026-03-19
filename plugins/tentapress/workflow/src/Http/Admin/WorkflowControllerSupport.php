<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Services\WorkflowResourceResolver;

trait WorkflowControllerSupport
{
    private function actor(): TpUser
    {
        $user = Auth::user();
        abort_unless($user instanceof TpUser, 403);

        return $user;
    }

    private function redirectToResource(string $resourceType, int $resourceId, string $message, string $level = 'success'): RedirectResponse
    {
        $resolver = app()->make(WorkflowResourceResolver::class);

        return redirect($resolver->editUrl($resourceType, $resourceId))
            ->with('tp_notice_'.$level, $message);
    }
}
