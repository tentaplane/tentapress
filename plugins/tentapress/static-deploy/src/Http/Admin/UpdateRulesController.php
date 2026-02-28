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

        $rules->save((string) $request->input('replacement_rules_json', '[]'));

        return to_route('tp.static.index')
            ->with('tp_notice_success', 'Static deploy replacement rules saved.');
    }
}
