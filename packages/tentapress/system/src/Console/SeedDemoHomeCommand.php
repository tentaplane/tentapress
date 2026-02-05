<?php

declare(strict_types=1);

namespace TentaPress\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use TentaPress\Pages\Models\TpPage;

final class SeedDemoHomeCommand extends Command
{
    protected $signature = 'tp:demo:seed-home
        {--slug=home : Page slug to create or update}
        {--title=Home : Page title}
        {--layout=default : Page layout key}
        {--status=published : Page status (draft|published)}';

    protected $description = 'Create or update the demo home page using the setup script demo blocks';

    public function handle(): int
    {
        if (! class_exists(TpPage::class)) {
            $this->error('Pages plugin class not found. Enable/install tentapress/pages first.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('tp_pages')) {
            $this->error('tp_pages table not found. Run migrations first.');

            return self::FAILURE;
        }

        try {
            $blocks = $this->loadDemoBlocksFromSetupScript();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $slug = trim((string) $this->option('slug'));
        $title = trim((string) $this->option('title'));
        $layout = trim((string) $this->option('layout'));
        $status = trim((string) $this->option('status'));

        if ($slug === '') {
            $this->error('--slug cannot be empty.');

            return self::FAILURE;
        }

        if ($title === '') {
            $this->error('--title cannot be empty.');

            return self::FAILURE;
        }

        if (! in_array($status, ['draft', 'published'], true)) {
            $this->error("--status must be 'draft' or 'published'.");

            return self::FAILURE;
        }

        $publishedAt = $status === 'published' ? now() : null;

        $payload = [
            'title' => $title,
            'status' => $status,
            'layout' => $layout !== '' ? $layout : null,
            'blocks' => $blocks,
            'published_at' => $publishedAt,
        ];

        $page = TpPage::query()->where('slug', $slug)->first();

        if ($page) {
            $page->fill($payload);
            $page->save();

            $this->info("Updated demo page '{$slug}' (id: {$page->id}) with ".count($blocks).' blocks.');

            return self::SUCCESS;
        }

        $page = TpPage::query()->create([
            'slug' => $slug,
            ...$payload,
        ]);

        $this->info("Created demo page '{$slug}' (id: {$page->id}) with ".count($blocks).' blocks.');

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadDemoBlocksFromSetupScript(): array
    {
        $path = base_path('tentapress.php');
        if (! is_file($path)) {
            throw new RuntimeException('Cannot find setup script at '.base_path('tentapress.php'));
        }

        $setup = file_get_contents($path);
        throw_if(! is_string($setup) || $setup === '', RuntimeException::class, 'Unable to read tentapress.php.');

        throw_unless(preg_match('/\$demoBlocks\s*=\s*\[(.*?)\n\s*];\n\n\s*\$blocksExport/s', $setup, $matches), RuntimeException::class, 'Could not locate $demoBlocks in tentapress.php.');

        $code = '['.$matches[1]."\n]";
        $blocks = eval('return '.$code.';');

        throw_unless(is_array($blocks), RuntimeException::class, 'Parsed demo blocks are invalid.');

        return array_values(array_filter($blocks, is_array(...)));
    }
}
