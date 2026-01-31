<?php

declare(strict_types=1);

namespace TentaPress\Posts\Console;

use Illuminate\Console\Command;
use TentaPress\Posts\Jobs\PublishScheduledPosts;
use Throwable;

final class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'tp:posts publish-scheduled {--limit=50 : Max posts to publish per run}';

    protected $description = 'Publish scheduled posts whose publish date has passed.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $limit = $limit > 0 ? $limit : 50;

        try {
            $published = (new PublishScheduledPosts($limit))->handle();

            if ($published === 0) {
                $this->info('No scheduled posts due for publishing.');
            } else {
                $this->info("Published {$published} scheduled post(s).");
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
