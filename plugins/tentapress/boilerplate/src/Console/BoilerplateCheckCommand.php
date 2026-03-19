<?php

declare(strict_types=1);

namespace TentaPress\Boilerplate\Console;

use Illuminate\Console\Command;
use TentaPress\Boilerplate\Services\BoilerplateSettings;

final class BoilerplateCheckCommand extends Command
{
    protected $signature = 'tp:boilerplate:check';

    protected $description = 'Show the current boilerplate plugin settings.';

    public function handle(BoilerplateSettings $settings): int
    {
        $this->components->twoColumnDetail('Enabled', $settings->isEnabled() ? 'yes' : 'no');
        $this->components->twoColumnDetail('Endpoint prefix', $settings->endpointPrefix());
        $this->components->twoColumnDetail('Admin notice', $settings->adminNotice());

        return self::SUCCESS;
    }
}
