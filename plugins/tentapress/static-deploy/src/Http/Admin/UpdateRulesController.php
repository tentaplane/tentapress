<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin;

use TentaPress\StaticDeploy\Http\Admin\Requests\UpdateRulesRequest;
use TentaPress\StaticDeploy\Support\StaticReplacementRules;

final readonly class UpdateRulesController
{
    public function __invoke(UpdateRulesRequest $request, StaticReplacementRules $rules)
    {
        if (! $rules->canPersist()) {
            return to_route('tp.static.index')
                ->with('tp_notice_warning', 'Static deploy settings are unavailable because the settings plugin is not enabled.');
        }

        $action = (string) $request->input('rules_action', 'save');
        $message = 'Static deploy replacement rules saved.';

        if ($action === 'load_example') {
            $rules->save($rules->exampleJson());
            $message = 'Example replacement rules loaded.';
        } elseif ($action === 'reset') {
            $rules->save($rules->emptyStateJson());
            $message = 'Replacement rules reset.';
        } else {
            $rules->save((string) $request->input('replacement_rules_json', '[]'));
        }

        return to_route('tp.static.index')
            ->with('tp_notice_success', $message);
    }
}
