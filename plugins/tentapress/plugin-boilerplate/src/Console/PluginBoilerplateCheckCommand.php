<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate\Console;

use Illuminate\Console\Command;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateSettings;

final class PluginBoilerplateCheckCommand extends Command
{
    protected $signature = 'tp:plugin-boilerplate:check';

    protected $description = 'Show the current boilerplate plugin settings.';

    public function handle(PluginBoilerplateSettings $settings): int
    {
        $this->components->twoColumnDetail('Enabled', $settings->isEnabled() ? 'yes' : 'no');
        $this->components->twoColumnDetail('Endpoint prefix', $settings->endpointPrefix());
        $this->components->twoColumnDetail('Admin notice', $settings->adminNotice());

        return self::SUCCESS;
    }
}
