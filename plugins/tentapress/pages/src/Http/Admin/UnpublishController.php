<?php

declare(strict_types=1);

namespace TentaPress\Pages\Http\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Services\WorkflowManager;

final class UnpublishController
{
    public function __invoke(TpPage $page)
    {
        if ($this->workflowPluginEnabled() && class_exists(WorkflowManager::class) && app()->bound(WorkflowManager::class)) {
            /** @var TpUser|null $actor */
            $actor = Auth::user();
            abort_unless($actor instanceof TpUser, 403);

            app()->make(WorkflowManager::class)->unpublish('pages', (int) $page->id, $actor);

            return to_route('tp.pages.edit', ['page' => $page->id])
                ->with('tp_notice_success', 'Page set to draft.');
        }

        $nowUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $page->status = 'draft';
        $page->updated_by = $nowUserId ?: null;
        $page->save();

        return to_route('tp.pages.edit', ['page' => $page->id])
            ->with('tp_notice_success', 'Page set to draft.');
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
