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
        return view('tentapress-static-deploy::index', [
            'last' => $exporter->lastBuildInfo(),
            'canPersistRules' => $rules->canPersist(),
            'replacementRulesJson' => old('replacement_rules_json', $rules->savedJson()),
            'replacementRulesExample' => $rules->exampleJson(),
        ]);
    }
}
