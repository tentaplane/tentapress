<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Console;

use Illuminate\Console\Command;
use TentaPress\Workflow\Services\WorkflowManager;

final class PublishScheduledWorkflowCommand extends Command
{
    protected $signature = 'tp:workflow:publish-scheduled {--limit=50 : Max workflow items to publish per run}';

    protected $description = 'Publish approved workflow items whose scheduled publish time has passed.';

    public function handle(WorkflowManager $manager): int
    {
        $published = $manager->publishDue((int) $this->option('limit'));

        if ($published === 0) {
            $this->info('No scheduled workflow items due for publishing.');

            return self::SUCCESS;
        }

        $this->info("Published {$published} scheduled workflow item(s).");

        return self::SUCCESS;
    }
}
