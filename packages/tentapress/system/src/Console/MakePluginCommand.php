<?php

declare(strict_types=1);

namespace TentaPress\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use TentaPress\System\Plugin\BoilerplateGenerator;
use TentaPress\System\Plugin\PluginRegistry;
use Throwable;

final class MakePluginCommand extends Command
{
    protected $signature = 'tp:plugin:make
        {vendor? : Plugin vendor, for example tentapress or acme}
        {slug? : Plugin slug in kebab-case}
        {name? : Human-readable plugin name}
        {--source=auto : Template source: auto, packagist, or local}
        {--template-package=tentapress/boilerplate : Packagist template package}
        {--template-version= : Specific Packagist template version}
        {--namespace= : Root PHP namespace, for example TentaPress\\ExamplePlugin}
        {--description= : Plugin description written to tentapress.json and README}
        {--enable : Enable the plugin after generating it}
    ';

    protected $description = 'Clone the boilerplate template into a new plugin package';

    public function __construct(
        private readonly BoilerplateGenerator $generator,
        private readonly PluginRegistry $registry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $vendor = $this->resolveVendor();
            $slug = $this->resolveSlug();
            $name = $this->resolveName($slug);
            $namespace = $this->resolveNamespace($vendor, $slug);
            $description = $this->resolveDescription($name);
            $source = $this->resolveSource();
            $templatePackage = trim((string) $this->option('template-package'));
            $templateVersion = trim((string) $this->option('template-version'));
            $templateVersion = $templateVersion === '' ? null : $templateVersion;

            $result = $this->generator->generate(
                vendor: $vendor,
                slug: $slug,
                name: $name,
                namespace: $namespace,
                description: $description,
                source: $source,
                templatePackage: $templatePackage,
                templateVersion: $templateVersion,
            );

            $this->registry->sync();
            $this->registry->writeCache();
            $this->callSilent('view:clear');

            if ((bool) $this->option('enable')) {
                $this->registry->enable($result['package_id']);
                $this->registry->writeCache();
                $this->callSilent('view:clear');
            }

            $this->info('Plugin generated successfully.');
            $this->line($result['path']);
            $this->line("Package: {$result['package_id']}");
            $this->line("Namespace: {$namespace}");
            $this->line("Template source: {$result['source']}");

            if (($result['fallback_reason'] ?? null) !== null) {
                $this->warn((string) $result['fallback_reason']);
            }

            if ((bool) $this->option('enable')) {
                $this->info("Enabled {$result['package_id']}.");
            } else {
                $this->line("Next: php artisan tp:plugins enable {$result['package_id']}");
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveVendor(): string
    {
        $vendor = trim((string) ($this->argument('vendor') ?? ''));

        if ($vendor === '' && $this->input->isInteractive()) {
            $vendor = trim((string) $this->ask('Plugin vendor', 'tentapress'));
        }

        throw_unless(preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $vendor), \InvalidArgumentException::class, 'Vendor must use lowercase kebab-case.');

        return $vendor;
    }

    private function resolveSlug(): string
    {
        $slug = trim((string) ($this->argument('slug') ?? ''));

        if ($slug === '' && $this->input->isInteractive()) {
            $slug = trim((string) $this->ask('Plugin slug', 'example-plugin'));
        }

        throw_unless(preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug), \InvalidArgumentException::class, 'Slug must use lowercase kebab-case.');

        return $slug;
    }

    private function resolveName(string $slug): string
    {
        $name = trim((string) ($this->argument('name') ?? ''));

        if ($name === '' && $this->input->isInteractive()) {
            $name = trim((string) $this->ask('Plugin name', Str::of($slug)->replace('-', ' ')->title()->toString()));
        }

        throw_if($name === '', \InvalidArgumentException::class, 'Plugin name cannot be empty.');

        return $name;
    }

    private function resolveNamespace(string $vendor, string $slug): string
    {
        $namespace = trim((string) ($this->option('namespace') ?? ''));

        if ($namespace === '' && $this->input->isInteractive()) {
            $namespace = trim((string) $this->ask('Root PHP namespace', $this->defaultNamespace($vendor, $slug)));
        }

        if ($namespace === '') {
            $namespace = $this->defaultNamespace($vendor, $slug);
        }

        throw_unless(preg_match('/^[A-Z][A-Za-z0-9]*(?:\\\\[A-Z][A-Za-z0-9]*)*$/', $namespace), \InvalidArgumentException::class, 'Namespace must use valid StudlyCase segments separated by backslashes.');

        return $namespace;
    }

    private function resolveDescription(string $name): string
    {
        $description = trim((string) ($this->option('description') ?? ''));

        if ($description === '' && $this->input->isInteractive()) {
            $description = trim((string) $this->ask('Plugin description', "Describe what {$name} does."));
        }

        if ($description === '') {
            $description = "Describe what {$name} does.";
        }

        return $description;
    }

    private function resolveSource(): string
    {
        $source = strtolower(trim((string) ($this->option('source') ?? 'auto')));

        throw_unless(in_array($source, ['auto', 'packagist', 'local'], true), \InvalidArgumentException::class, 'Source must be one of: auto, packagist, local.');

        return $source;
    }

    private function defaultNamespace(string $vendor, string $slug): string
    {
        return $this->studlySegment($vendor).'\\'.$this->studlySegment($slug);
    }

    private function studlySegment(string $value): string
    {
        return Str::of($value)
            ->replace(['-', '_'], ' ')
            ->studly()
            ->toString();
    }
}
