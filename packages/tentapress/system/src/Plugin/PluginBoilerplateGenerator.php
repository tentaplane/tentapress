<?php

declare(strict_types=1);

namespace TentaPress\System\Plugin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use TentaPress\System\Support\Paths;
use ZipArchive;

final class PluginBoilerplateGenerator
{
    /**
     * @return array{package_id:string,path:string,source:string,fallback_reason:?string}
     */
    public function generate(
        string $vendor,
        string $slug,
        string $name,
        string $namespace,
        string $description,
        string $source = 'auto',
        string $templatePackage = 'tentapress/plugin-boilerplate',
        ?string $templateVersion = null,
    ): array {
        $destinationPath = Paths::pluginsPath($vendor.'/'.$slug);
        $packageId = $vendor.'/'.$slug;
        $resolvedTemplate = $this->resolveTemplatePath(
            source: $source,
            templatePackage: $templatePackage,
            templateVersion: $templateVersion,
        );

        throw_if(File::exists($destinationPath), InvalidArgumentException::class, "Destination already exists: {$destinationPath}");

        File::ensureDirectoryExists(dirname($destinationPath));

        throw_unless(File::copyDirectory($resolvedTemplate['path'], $destinationPath), InvalidArgumentException::class, "Unable to copy boilerplate into {$destinationPath}");

        $replacements = $this->replacementMap(
            vendor: $vendor,
            slug: $slug,
            name: $name,
            namespace: $namespace,
            description: $description,
        );

        $this->rewriteFileContents($destinationPath, $replacements);
        $this->renameGeneratedPaths($destinationPath, $replacements['PluginBoilerplate']);

        if (($resolvedTemplate['cleanup_path'] ?? null) !== null) {
            File::deleteDirectory((string) $resolvedTemplate['cleanup_path']);
        }

        return [
            'package_id' => $packageId,
            'path' => $destinationPath,
            'source' => (string) $resolvedTemplate['source'],
            'fallback_reason' => $resolvedTemplate['fallback_reason'] ?? null,
        ];
    }

    /**
     * @return array{path:string,source:string,cleanup_path:?string,fallback_reason:?string}
     */
    private function resolveTemplatePath(
        string $source,
        string $templatePackage,
        ?string $templateVersion,
    ): array {
        return match ($source) {
            'local' => [
                'path' => $this->localTemplatePath(),
                'source' => 'local',
                'cleanup_path' => null,
                'fallback_reason' => null,
            ],
            'packagist' => $this->downloadPackagistTemplate($templatePackage, $templateVersion),
            default => $this->resolveAutoTemplatePath($templatePackage, $templateVersion),
        };
    }

    /**
     * @return array{path:string,source:string,cleanup_path:?string,fallback_reason:?string}
     */
    private function resolveAutoTemplatePath(string $templatePackage, ?string $templateVersion): array
    {
        try {
            return $this->downloadPackagistTemplate($templatePackage, $templateVersion);
        } catch (InvalidArgumentException $exception) {
            return [
                'path' => $this->localTemplatePath(),
                'source' => 'local',
                'cleanup_path' => null,
                'fallback_reason' => "Packagist template unavailable - falling back to local boilerplate source. {$exception->getMessage()}",
            ];
        }
    }

    private function localTemplatePath(): string
    {
        $sourcePath = Paths::pluginsPath('tentapress/plugin-boilerplate');

        throw_unless(File::isDirectory($sourcePath), InvalidArgumentException::class, 'Plugin boilerplate source directory was not found.');

        return $sourcePath;
    }

    /**
     * @return array{path:string,source:string,cleanup_path:?string,fallback_reason:?string}
     */
    private function downloadPackagistTemplate(string $templatePackage, ?string $templateVersion): array
    {
        $package = strtolower(trim($templatePackage));

        throw_if($package === '', InvalidArgumentException::class, 'Template package cannot be empty.');

        $metadataResponse = Http::timeout(20)
            ->acceptJson()
            ->get('https://repo.packagist.org/p2/'.rawurlencode($package).'.json');

        throw_unless($metadataResponse->successful(), InvalidArgumentException::class, "Packagist metadata not found for {$package}.");

        $payload = $metadataResponse->json();
        $releases = $payload['packages'][$package] ?? null;

        throw_unless(is_array($releases) && $releases !== [], InvalidArgumentException::class, "No Packagist releases found for {$package}.");

        $release = $this->selectRelease($releases, $templateVersion);
        $distUrl = $release['dist']['url'] ?? null;

        throw_unless(is_string($distUrl) && $distUrl !== '', InvalidArgumentException::class, "No downloadable dist archive found for {$package}.");

        $temporaryRoot = storage_path('app/tp-plugin-boilerplate/'.Str::uuid()->toString());
        $archivePath = $temporaryRoot.'/template.zip';
        $extractPath = $temporaryRoot.'/extract';

        File::ensureDirectoryExists($temporaryRoot);
        File::ensureDirectoryExists($extractPath);

        $archiveResponse = Http::timeout(60)->get($distUrl);

        throw_unless($archiveResponse->successful(), InvalidArgumentException::class, "Unable to download Packagist archive for {$package}.");

        File::put($archivePath, $archiveResponse->body());

        $zip = new ZipArchive();
        throw_unless($zip->open($archivePath) === true, InvalidArgumentException::class, "Unable to open Packagist archive for {$package}.");
        $zip->extractTo($extractPath);
        $zip->close();

        return [
            'path' => $this->normaliseExtractedTemplateRoot($extractPath),
            'source' => 'packagist',
            'cleanup_path' => $temporaryRoot,
            'fallback_reason' => null,
        ];
    }

    /**
     * @param  array<int,array<string,mixed>>  $releases
     * @return array<string,mixed>
     */
    private function selectRelease(array $releases, ?string $templateVersion): array
    {
        if ($templateVersion !== null) {
            foreach ($releases as $release) {
                if (($release['version'] ?? null) === $templateVersion) {
                    return $release;
                }
            }

            throw new InvalidArgumentException("Version {$templateVersion} was not found in Packagist metadata.");
        }

        foreach ($releases as $release) {
            $version = strtolower((string) ($release['version'] ?? ''));

            if ($version !== '' && ! str_contains($version, 'dev')) {
                return $release;
            }
        }

        return $releases[0];
    }

    private function normaliseExtractedTemplateRoot(string $extractPath): string
    {
        $entries = array_values(array_filter(
            File::directories($extractPath),
            static fn (string $path): bool => File::exists($path.'/composer.json')
        ));

        if (count($entries) === 1) {
            return $entries[0];
        }

        throw_unless(File::exists($extractPath.'/composer.json'), InvalidArgumentException::class, 'Extracted Packagist archive did not contain a plugin template root.');

        return $extractPath;
    }

    /**
     * @return array<string,string>
     */
    private function replacementMap(
        string $vendor,
        string $slug,
        string $name,
        string $namespace,
        string $description,
    ): array {
        $snakeSlug = Str::of($slug)->replace('-', '_')->toString();
        $studlySlug = $this->namespaceLeaf($namespace);
        $viewNamespace = $vendor.'-'.$slug;
        $capability = 'manage_'.$snakeSlug;
        $escapedNamespace = str_replace('\\', '\\\\', $namespace);

        return [
            'Cloneable first-party plugin starter for TentaPress.' => $description,
            'Cloneable first-party plugin starter' => $name,
            'Plugin Boilerplate' => $name,
            'Plugin boilerplate' => $name,
            'Manage Plugin Boilerplate' => 'Manage '.$name,
            'Show the current boilerplate plugin settings.' => "Show the current {$name} plugin settings.",
            'Use this starter as the baseline for new first-party TentaPress plugins.' => "Use {$name} as the baseline for this plugin.",
            'Simple example setting stored via the shared settings plugin.' => "Example setting storage for {$name}.",
            'Example content field for a plugin-owned admin view.' => "Example content field for {$name}.",
            'Plugin boilerplate settings saved.' => "{$name} settings saved.",
            'tentapress/plugin-boilerplate' => $vendor.'/'.$slug,
            'tentapress-plugin-boilerplate' => $viewNamespace,
            'plugin-boilerplate' => $slug,
            'plugin_boilerplate' => $snakeSlug,
            'TentaPress\\\\PluginBoilerplate\\\\' => $escapedNamespace.'\\\\',
            'TentaPress\\\\PluginBoilerplate' => $escapedNamespace,
            'PluginBoilerplate' => $studlySlug,
            'TentaPress\\PluginBoilerplate' => $namespace,
            'manage_plugin_boilerplate' => $capability,
        ];
    }

    /**
     * @param  array<string,string>  $replacements
     */
    private function rewriteFileContents(string $destinationPath, array $replacements): void
    {
        uksort($replacements, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        foreach (File::allFiles($destinationPath) as $file) {
            $contents = File::get($file->getPathname());
            $rewritten = str_replace(array_keys($replacements), array_values($replacements), $contents);

            if ($rewritten !== $contents) {
                File::put($file->getPathname(), $rewritten);
            }
        }
    }

    private function renameGeneratedPaths(string $destinationPath, string $classStem): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($destinationPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $path = $item->getPathname();
            $basename = basename((string) $path);
            $rewrittenBasename = str_replace('PluginBoilerplate', $classStem, $basename);

            if ($basename === $rewrittenBasename) {
                continue;
            }

            File::move($path, dirname((string) $path).DIRECTORY_SEPARATOR.$rewrittenBasename);
        }
    }

    private function namespaceLeaf(string $namespace): string
    {
        $leaf = Str::afterLast($namespace, '\\');

        throw_if($leaf === '', InvalidArgumentException::class, 'Namespace must contain at least one segment.');

        return $leaf;
    }
}
