<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Services\WorkflowManager;

final class PublishController
{
    public function __invoke(TpPage $page)
    {
        if ($this->workflowPluginEnabled() && class_exists(WorkflowManager::class) && app()->bound(WorkflowManager::class)) {
            /** @var TpUser|null $actor */
            $actor = Auth::user();
            abort_unless($actor instanceof TpUser, 403);

            try {
                app()->make(WorkflowManager::class)->publishNow('pages', (int) $page->id, $actor);
            } catch (InvalidArgumentException $exception) {
                return to_route('tp.pages.edit', ['page' => $page->id])
                    ->with('tp_notice_error', $exception->getMessage());
            }

            return to_route('tp.pages.edit', ['page' => $page->id])
                ->with('tp_notice_success', 'Page published.');
        }

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $page->status = 'published';
        $page->published_at = $page->published_at ?: now();
        $page->updated_by = $nowUserId ?: null;
        $page->save();

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page published.');
    }

    private function workflowPluginEnabled(): bool
    {
        if (! Schema::hasTable('tp_plugins')) {
            return false;
        }

        return (int) DB::table('tp_plugins')
            ->where('id', 'tentapress/workflow')
            ->value('enabled') === 1;
    }
}
