<?php

declare(strict_types=1);

namespace TentaPress\Forms\Console;

use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use Illuminate\Console\Command;

final class MigrateNewsletterBlocksCommand extends Command
{
    protected $signature = 'tp:forms:migrate-newsletter {--dry-run : Preview changes without saving}';

    protected $description = 'Migrate legacy blocks/newsletter entries to forms/signup blocks on pages and posts.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'Running in dry-run mode. No records will be saved.' : 'Migrating newsletter blocks.');

        $pageChanges = $this->migrateModel(TpPage::class);
        $postChanges = $this->migrateModel(TpPost::class);

        $total = $pageChanges + $postChanges;

        $this->line('Pages changed: '.$pageChanges);
        $this->line('Posts changed: '.$postChanges);
        $this->info('Total changed: '.$total);

        return self::SUCCESS;
    }

    private function migrateModel(string $modelClass): int
    {
        if (! class_exists($modelClass)) {
            return 0;
        }

        $changed = 0;
        $dryRun = (bool) $this->option('dry-run');

        $modelClass::query()->orderBy('id')->chunkById(200, function ($items) use (&$changed, $dryRun): void {
            foreach ($items as $item) {
                $blocks = $item->blocks;
                [$nextBlocks, $didChange] = $this->migrateBlocks($blocks);

                if (! $didChange) {
                    continue;
                }

                $changed++;

                if ($dryRun) {
                    continue;
                }

                $item->blocks = $nextBlocks;
                $item->save();
            }
        });

        return $changed;
    }

    /**
     * @return array{0:mixed,1:bool}
     */
    private function migrateBlocks(mixed $blocks): array
    {
        if (! is_array($blocks)) {
            return [$blocks, false];
        }

        if (array_key_exists('blocks', $blocks) && is_array($blocks['blocks'])) {
            [$nextList, $didChange] = $this->migrateBlockList($blocks['blocks']);

            if (! $didChange) {
                return [$blocks, false];
            }

            $next = $blocks;
            $next['blocks'] = $nextList;

            return [$next, true];
        }

        [$nextList, $didChange] = $this->migrateBlockList($blocks);

        return [$nextList, $didChange];
    }

    /**
     * @param  array<int,mixed>  $blocks
     * @return array{0:array<int,mixed>,1:bool}
     */
    private function migrateBlockList(array $blocks): array
    {
        $changed = false;
        $next = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                $next[] = $block;
                continue;
            }

            $type = trim((string) ($block['type'] ?? ''));

            if ($type !== 'blocks/newsletter') {
                $next[] = $block;
                continue;
            }

            $next[] = $this->toFormsBlock($block);
            $changed = true;
        }

        return [$next, $changed];
    }

    /**
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    private function toFormsBlock(array $block): array
    {
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        $actions = $this->parseActions($props['actions'] ?? []);
        $primaryAction = $actions[0] ?? [];

        $submitLabel = trim((string) ($primaryAction['label'] ?? 'Subscribe'));
        if ($submitLabel === '') {
            $submitLabel = 'Subscribe';
        }

        $actionUrl = trim((string) ($primaryAction['url'] ?? ''));

        $formKey = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9._-]/', '-', (string) ($props['form_key'] ?? 'newsletter-signup')), '-'));
        if ($formKey === '') {
            $formKey = 'newsletter-signup';
        }

        $placeholder = trim((string) ($props['email_placeholder'] ?? 'you@example.com'));
        if ($placeholder === '') {
            $placeholder = 'you@example.com';
        }

        return [
            'type' => 'forms/signup',
            'version' => 1,
            'props' => [
                'form_key' => $formKey,
                'title' => (string) ($props['title'] ?? ''),
                'description' => (string) ($props['body'] ?? ''),
                'fields' => [
                    [
                        'key' => 'email',
                        'label' => 'Email',
                        'type' => 'email',
                        'required' => '1',
                        'placeholder' => $placeholder,
                        'default' => '',
                        'options' => '',
                        'merge_tag' => 'EMAIL',
                    ],
                ],
                'submit_label' => $submitLabel,
                'success_message' => 'Thanks for subscribing.',
                'error_message' => 'We could not submit your form. Please try again.',
                'redirect_url' => '',
                'privacy_notice' => (string) ($props['disclaimer'] ?? ''),
                'provider' => 'mailchimp',
                'mailchimp_action_url' => $actionUrl,
                'mailchimp_list_id' => '',
                'mailchimp_gdpr_tag' => '',
                'tentaforms_form_id' => '',
                'tentaforms_environment' => 'production',
                'provider_config' => '',
            ],
        ];
    }

    /**
     * @return array<int,array{label:string,url:string,style:string}>
     */
    private function parseActions(mixed $rawActions): array
    {
        if (is_string($rawActions)) {
            $trimmed = trim($rawActions);

            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $rawActions = $decoded;
            } else {
                $lines = preg_split('/\r?\n/', $trimmed) ?: [];
                $fromLines = [];

                foreach ($lines as $line) {
                    $line = trim((string) $line);

                    if ($line === '') {
                        continue;
                    }

                    $parts = array_map(trim(...), explode('|', $line));
                    $fromLines[] = [
                        'label' => (string) ($parts[0] ?? $line),
                        'url' => (string) ($parts[1] ?? ''),
                        'style' => (string) ($parts[2] ?? 'primary'),
                    ];
                }

                return $fromLines;
            }
        }

        if (! is_array($rawActions)) {
            return [];
        }

        $out = [];

        foreach ($rawActions as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $out[] = [
                'label' => $label,
                'url' => trim((string) ($item['url'] ?? '')),
                'style' => trim((string) ($item['style'] ?? 'primary')),
            ];
        }

        return $out;
    }
}
