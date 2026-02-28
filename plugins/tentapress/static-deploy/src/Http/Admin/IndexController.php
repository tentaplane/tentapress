<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin;

use TentaPress\StaticDeploy\Services\StaticExporter;
use TentaPress\StaticDeploy\Support\StaticReplacementRules;

final class IndexController
{
    public function __invoke(
        StaticExporter $exporter,
        StaticReplacementRules $rules
    ) {
        $replacementRulesJson = old('replacement_rules_json', $rules->savedJson());
        $savedRuleCount = 0;

        try {
            $savedRuleCount = count(StaticReplacementRules::normalize($replacementRulesJson));
        } catch (\InvalidArgumentException) {
            $savedRuleCount = 0;
        }

        return view('tentapress-static-deploy::index', [
            'last' => $exporter->lastBuildInfo(),
            'canPersistRules' => $rules->canPersist(),
            'replacementRulesJson' => $replacementRulesJson,
            'replacementRulesExample' => $rules->exampleJson(),
            'savedReplacementRuleCount' => $savedRuleCount,
        ]);
    }
}
