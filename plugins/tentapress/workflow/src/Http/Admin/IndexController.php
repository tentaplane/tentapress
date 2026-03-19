<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use TentaPress\Workflow\Services\WorkflowManager;
use TentaPress\Workflow\Services\WorkflowResourceResolver;

final class IndexController
{
    use WorkflowControllerSupport;

    public function __invoke(Request $request, WorkflowManager $manager, WorkflowResourceResolver $resources): View
    {
        $filter = in_array((string) $request->query('filter', 'all'), ['all', 'mine', 'review', 'scheduled'], true)
            ? (string) $request->query('filter', 'all')
            : 'all';

        $items = $manager->queue($filter, $this->actor());

        return view('tentapress-workflow::workflow.index', [
            'items' => $items,
            'filter' => $filter,
            'resources' => $resources,
        ]);
    }
}
