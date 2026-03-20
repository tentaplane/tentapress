<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Services;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Support\WorkflowPluginState;

final readonly class WorkflowFormComposer
{
    public function __construct(
        private WorkflowManager $manager,
    ) {
    }

    public function compose(View $view): void
    {
        if (! WorkflowPluginState::isEnabled()) {
            $view->with('workflowPluginEnabled', false);

            return;
        }

        $data = $view->getData();
        $page = $data['page'] ?? null;
        $post = $data['post'] ?? null;

        $resourceType = null;
        $resource = null;

        if (is_object($page) && isset($page->id)) {
            $resourceType = 'pages';
            $resource = $page;
        }

        if (is_object($post) && isset($post->id)) {
            $resourceType = 'posts';
            $resource = $post;
        }

        if ($resourceType === null || ! is_object($resource)) {
            $view->with('workflowPluginEnabled', false);

            return;
        }

        $resourceId = (int) ($resource->id ?? 0);
        if ($resourceId <= 0) {
            $view->with('workflowPluginEnabled', false);

            return;
        }

        $currentUserId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;
        $workflowItem = $this->manager->ensureForResource($resourceType, $resourceId, $currentUserId);
        $pendingRevision = $this->manager->pendingRevisionFor($resourceType, $resourceId);

        $view->with('workflowPluginEnabled', true);
        $view->with('workflowItem', $workflowItem);
        $view->with('workflowPendingRevision', $pendingRevision);
        $view->with('workflowResourceType', $resourceType);
        $view->with('workflowUsers', $this->workflowUsers());

        if (($data['loadedAutosave'] ?? null) !== null || $pendingRevision === null) {
            return;
        }

        $view->with('formTitle', (string) $pendingRevision->title);
        $view->with('formSlug', (string) $pendingRevision->slug);
        $view->with('formLayout', (string) ($pendingRevision->layout ?? ''));
        $view->with('formEditorDriver', (string) ($pendingRevision->editor_driver ?? 'blocks'));
        $view->with('formPublishedAt', $pendingRevision->published_at?->format('Y-m-d\\TH:i'));
        $view->with('blocksJson', $this->encodeArray($pendingRevision->blocks));
        $view->with('pageDocJson', $this->encodeNullableArray($pendingRevision->content));

        if ($resourceType === 'posts') {
            $authorId = $pendingRevision->author_id !== null ? (int) $pendingRevision->author_id : null;
            $view->with('authorId', $authorId);
        }
    }

    /**
     * @return array<int,TpUser>
     */
    private function workflowUsers(): array
    {
        if (! Schema::hasTable('tp_users')) {
            return [];
        }

        return TpUser::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->all();
    }

    /**
     * @param array<int|string,mixed>|null $payload
     */
    private function encodeArray(?array $payload): string
    {
        $encoded = json_encode(is_array($payload) ? $payload : [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '[]' : $encoded;
    }

    /**
     * @param array<int|string,mixed>|null $payload
     */
    private function encodeNullableArray(?array $payload): string
    {
        $fallback = ['time' => 0, 'blocks' => [], 'version' => '2.28.0'];
        $encoded = json_encode(is_array($payload) ? $payload : $fallback, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? '{"time":0,"blocks":[],"version":"2.28.0"}' : $encoded;
    }
}
