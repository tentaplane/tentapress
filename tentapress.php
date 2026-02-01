#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$root = __DIR__;
$artisanPath = $root . '/artisan';

if (! is_file($artisanPath)) {
    fwrite(STDERR, "Unable to find artisan at {$artisanPath}. Run this from the root folder.\n");
    exit(1);
}

$command = $argv[1] ?? '';
$skipUser = in_array('--no-user', $argv, true);

fwrite(
    STDOUT,
    '  _______         _        _____
 |__   __|       | |      |  __ \
    | | ___ _ __ | |_ __ _| |__) | __ ___  ___ ___
    | |/ _ \ \'_ \| __/ _` |  ___/ \'__/ _ \/ __/ __|
    | |  __/ | | | || (_| | |   | | |  __/\__ \__ \
    |_|\___|_| |_|\__\__,_|_|   |_|  \___||___/___/' . "\n\n"
);

if ($command !== 'setup') {
    fwrite(STDOUT, "Usage: php tentapress.php setup [--no-user]\n");
    exit($command === '' ? 0 : 1);
}

$run = static function (string $shellCommand, string $label): void {
    fwrite(STDOUT, "\n{$label}\n");
    passthru($shellCommand, $status);

    if ($status !== 0) {
        exit((int) $status);
    }
};

$prompt = static function (string $label): string {
    fwrite(STDOUT, $label);

    $line = fgets(STDIN);

    return $line === false ? '' : trim($line);
};

$promptChoice = static function (string $label, array $choices, string $default) use ($prompt): string {
    while (true) {
        $choice = strtolower($prompt($label));

        if ($choice === '') {
            return $default;
        }

        if (array_key_exists($choice, $choices)) {
            return $choices[$choice];
        }

        fwrite(STDOUT, 'Please choose ' . implode(', ', array_keys($choices)) . ".\n");
    }
};

$loadInstalledPackages = static function () use ($root): array {
    $installedPath = $root . '/vendor/composer/installed.php';

    if (is_file($installedPath)) {
        $installed = require $installedPath;

        if (is_array($installed)) {
            if (isset($installed['packages']) && is_array($installed['packages'])) {
                return $installed['packages'];
            }

            if (isset($installed['versions']) && is_array($installed['versions'])) {
                $packages = [];

                foreach ($installed['versions'] as $name => $version) {
                    if (is_array($version)) {
                        $version['name'] = $name;
                        $packages[] = $version;
                    }
                }

                return $packages;
            }

            if ($installed !== []) {
                return $installed;
            }
        }
    }

    $installedJson = $root . '/vendor/composer/installed.json';
    if (is_file($installedJson)) {
        $raw = file_get_contents($installedJson);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($decoded)) {
            return [];
        }

        if (isset($decoded['packages']) && is_array($decoded['packages'])) {
            return $decoded['packages'];
        }

        if (isset($decoded[0]['packages']) && is_array($decoded[0]['packages'])) {
            return $decoded[0]['packages'];
        }

        return $decoded;
    }

    return [];
};

$resolvePackagePath = static function (string $packageName) use ($loadInstalledPackages, $root): ?string {
    foreach ($loadInstalledPackages() as $package) {
        if (! is_array($package)) {
            continue;
        }

        if (($package['name'] ?? null) !== $packageName) {
            continue;
        }

        $installPath = $package['install_path'] ?? $package['install-path'] ?? null;

        if (is_string($installPath) && $installPath !== '') {
            return $installPath;
        }
    }

    $vendorPath = $root . '/vendor/' . $packageName;

    if (is_dir($vendorPath)) {
        return $vendorPath;
    }

    return null;
};

$copyThemeFromVendor = static function (string $packageName) use ($prompt, $resolvePackagePath, $root): ?array {
    $installPath = $resolvePackagePath($packageName);

    if ($installPath === null) {
        fwrite(STDOUT, "Unable to locate installed package {$packageName}.\n");
        return null;
    }

    $manifestPath = rtrim($installPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tentapress.json';

    if (! is_file($manifestPath)) {
        fwrite(STDOUT, "Missing tentapress.json in {$packageName}.\n");
        return null;
    }

    $manifestRaw = file_get_contents($manifestPath);
    $manifest = is_string($manifestRaw) ? json_decode($manifestRaw, true) : null;
    $themeId = is_array($manifest) ? (string) ($manifest['id'] ?? '') : '';

    if ($themeId === '' || ! str_contains($themeId, '/')) {
        $themeId = $packageName;
    }

    [$vendor, $name] = array_pad(explode('/', $themeId, 2), 2, '');

    if ($vendor === '' || $name === '') {
        fwrite(STDOUT, "Invalid theme id for {$packageName}.\n");
        return null;
    }

    $destination = $root . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $vendor . DIRECTORY_SEPARATOR . $name;

    if (is_dir($destination)) {
        $overwrite = strtolower($prompt("Theme already exists at themes/{$vendor}/{$name}. Overwrite? [y/N]: "));

        if (! in_array($overwrite, ['y', 'yes'], true)) {
            return $themeId;
        }
    }

    $skip = ['.git', 'node_modules', 'vendor'];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($installPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = $iterator->getSubPathName();
        $parts = explode(DIRECTORY_SEPARATOR, $relative);

        if ($parts !== [] && in_array($parts[0], $skip, true)) {
            $iterator->next();
            continue;
        }

        $targetPath = $destination . DIRECTORY_SEPARATOR . $relative;

        if ($item->isDir()) {
            if (! is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            continue;
        }

        $targetDir = dirname($targetPath);

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        copy($item->getPathname(), $targetPath);
    }

    return [
        'id' => $themeId,
        'install_path' => $installPath,
    ];
};

$resolveCommands = static function (array $candidates): array {
    $available = [];

    foreach ($candidates as $candidate) {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null'));

        if ($path !== '') {
            $available[] = $candidate;
        }
    }

    return $available;
};

$composerPath = trim((string) shell_exec('command -v composer 2>/dev/null'));

if ($composerPath !== '') {
    $composerCommand = escapeshellarg($composerPath);
} else {
    $localComposer = $root . '/composer.phar';

    if (is_file($localComposer)) {
        $composerCommand = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($localComposer);
    } else {
        fwrite(STDERR, "Composer not found. Install Composer or place composer.phar in the repo root.\n");
        exit(1);
    }
}

$hasComposerLock = file_exists($root . '/composer.lock');
$hasVendorFolder = is_dir($root . '/vendor');

if (! $hasComposerLock && ! $hasVendorFolder) {
    $envFile = $root . '/.env';
    $exampleEnvFile = $root . '/.env.example';

    if (! file_exists($envFile) && file_exists($exampleEnvFile)) {
        $run(
            escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg("file_exists('.env') || copy('.env.example', '.env');"),
            'Creating .env...'
        );
    }

    $sqlitePath = $root . '/database/database.sqlite';

    if (! file_exists($sqlitePath)) {
        $run(
            escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg("file_exists('database/database.sqlite') || touch('database/database.sqlite');"),
            'Creating SQLite database file...'
        );
    }

    $run(
        $composerCommand . ' install --no-dev --no-interaction --optimize-autoloader --no-scripts',
        'Installing Composer dependencies...'
    );

    $run($composerCommand . ' run post-autoload-dump', 'Running Composer post-autoload-dump scripts...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' key:generate', 'Generating app key...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' migrate --force', 'Running initial migrations...');
    $run($composerCommand . ' run post-update-cmd', 'Running Composer post-update-cmd scripts...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' storage:link', 'Linking your local storage files...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:plugins defaults --no-interaction', 'Applying default plugins...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' migrate --force', 'Running migrations (post plugins installation)...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:plugins enable --all', 'Enabling all default plugins...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:permissions seed', 'Seeding permissions...');
} else {
    fwrite(
        STDOUT,
        "Detected existing Composer install (composer.lock or vendor/ present).\n" .
        "Skipping Composer install and setup steps, proceeding to admin creation.\n\n"
    );
}

$themeChoice = $promptChoice(
    "Install a theme? [tailwind/bootstrap/none] (default: none): ",
    [
        'tailwind' => 'tentaplane/theme-tailwind',
        'bootstrap' => 'tentaplane/theme-bootstrap',
        'none' => 'none',
    ],
    'none'
);

if ($themeChoice !== 'none') {
    $run(
        $composerCommand . ' require ' . escapeshellarg($themeChoice) . ' --no-interaction --no-scripts',
        "Installing theme package {$themeChoice}..."
    );
    $themeCopy = $copyThemeFromVendor($themeChoice);
    $themeId = is_array($themeCopy) ? (string) ($themeCopy['id'] ?? $themeChoice) : $themeChoice;
    $themeInstallPath = is_array($themeCopy) ? ($themeCopy['install_path'] ?? null) : null;
    $themePath = $root . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $themeId);
    $buildTool = null;
    $shouldBuildAssets = false;
    $run($composerCommand . ' run post-autoload-dump', 'Running Composer post-autoload-dump scripts...');
    $run($composerCommand . ' run post-update-cmd', 'Running Composer post-update-cmd scripts...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes sync', 'Syncing themes...');
    $run(
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes activate ' . escapeshellarg($themeId),
        "Activating theme {$themeId}..."
    );

    $buildAssets = strtolower($prompt('Build theme assets now? [Y/n]: '));

    if (in_array($buildAssets, ['y', 'yes'], true)) {
        $shouldBuildAssets = true;
        $availableTools = $resolveCommands(['bun', 'pnpm', 'npm']);

        if ($availableTools === []) {
            fwrite(STDOUT, "No bun/pnpm/npm detected. Skipping theme asset build.\n");
            $shouldBuildAssets = false;
        } else {
            $buildTool = $availableTools[0];
            if (count($availableTools) > 1) {
                $buildTool = $promptChoice(
                    'Select a package manager [' . implode('/', $availableTools) . ']: ',
                    array_combine($availableTools, $availableTools),
                    $availableTools[0]
                );
            }
        }
    }

    $seedDemo = strtolower($prompt('Create a demo home page with sample blocks? [Y/n]: '));

    if ($seedDemo === '' || in_array($seedDemo, ['y', 'yes'], true)) {
        if (is_string($themeInstallPath) && is_dir($themeInstallPath)) {
            $sourceBlocksPath = $themeInstallPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'blocks';
            $targetBlocksPath = $themePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'blocks';
            $demoBlockViews = ['hero', 'logo-cloud', 'features', 'stats', 'testimonial', 'cta'];

            if (is_dir($sourceBlocksPath)) {
                if (! is_dir($targetBlocksPath)) {
                    mkdir($targetBlocksPath, 0755, true);
                }

                foreach ($demoBlockViews as $blockView) {
                    $sourceFile = $sourceBlocksPath . DIRECTORY_SEPARATOR . $blockView . '.blade.php';
                    $targetFile = $targetBlocksPath . DIRECTORY_SEPARATOR . $blockView . '.blade.php';

                    if (is_file($sourceFile) && ! is_file($targetFile)) {
                        copy($sourceFile, $targetFile);
                    }
                }
            }
        }

        $demoBlocks = [
            [
                'type' => 'blocks/hero',
                'variant' => 'split',
                'props' => [
                    'eyebrow' => 'Introducing TentaPress',
                    'headline' => "Launch standout websites in days, not weeks.",
                    'subheadline' => 'A modern block editor with a clean admin and a fast publishing workflow.',
                    'alignment' => 'left',
                    'image_position' => 'right',
                    'primary_cta' => [
                        'label' => 'Start free',
                        'url' => '/admin',
                        'style' => 'primary',
                    ],
                    'secondary_cta' => [
                        'label' => 'View docs',
                        'url' => '#features',
                    ],
                ],
            ],
            [
                'type' => 'blocks/features',
                'props' => [
                    'title' => 'Everything you need to launch',
                    'subtitle' => 'Compose pages fast with clean blocks, predictable layouts, and powerful settings.',
                    'items' => [
                        ['title' => 'Instant layout system', 'body' => 'Stack blocks and ship without touching code.', 'icon' => '⚡'],
                        ['title' => 'Theme-ready styling', 'body' => 'Utilities that keep the design consistent.', 'icon' => '🎨'],
                        ['title' => 'Publishing workflow', 'body' => 'Draft, preview, and publish with confidence.', 'icon' => '🚀'],
                        ['title' => 'Composable sections', 'body' => 'Mix hero, features, stats, and CTA blocks.', 'icon' => '🧩'],
                        ['title' => 'Built for teams', 'body' => 'Clear admin screens and safe editing modes.', 'icon' => '🤝'],
                        ['title' => 'Laravel-native', 'body' => 'Leverage a framework you already trust.', 'icon' => '🛡️'],
                    ],
                    'columns' => '3',
                ],
            ],
            [
                'type' => 'blocks/stats',
                'props' => [
                    'title' => 'By the numbers',
                    'items' => [
                        ['value' => '10 x', 'label' => 'Faster publishing'],
                        ['value' => '99.9%', 'label' => 'Uptime target'],
                        ['value' => '5 mins', 'label' => 'Average page creation'],
                    ],
                    'columns' => '3',
                    'dividers' => true,
                ],
            ],
            [
                'type' => 'blocks/testimonial',
                'props' => [
                    'quote' => 'We replaced three tools with TentaPress and shipped our new website in a week!',
                    'name' => 'Test User',
                    'role' => 'Head of Growth, Demo Company',
                    'rating' => 5,
                    'alignment' => 'left',
                    'style' => 'card',
                ],
            ],
            [
                'type' => 'blocks/cta',
                'props' => [
                    'title' => 'Launch your next site today',
                    'body' => 'Start with a polished theme and refine your blocks as you grow.',
                    'alignment' => 'left',
                    'background' => 'muted',
                    'button' => [
                        'label' => 'Get started',
                        'url' => '/admin',
                        'style' => 'primary',
                    ],
                    'secondary_button' => [
                        'label' => 'Your admin login',
                        'url' => '/admin',
                    ],
                ],
            ],
        ];

        $blocksExport = var_export($demoBlocks, true);
        $demoScript = <<<PHP
require __DIR__ . '/vendor/autoload.php';

\$app = require __DIR__ . '/bootstrap/app.php';
\$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

use Illuminate\\Support\\Facades\\Schema;
use TentaPress\\Pages\\Models\\TpPage;

if (!class_exists(TpPage::class) || !Schema::hasTable('tp_pages')) {
    return;
}

if (TpPage::query()->where('slug', 'home')->exists()) {
    return;
}

TpPage::query()->create([
    'title' => 'Home',
    'slug' => 'home',
    'status' => 'published',
    'layout' => 'default',
    'blocks' => {$blocksExport},
    'published_at' => now(),
]);
PHP;

        $run(
            escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($demoScript),
            'Creating demo home page...'
        );
    }

    if ($shouldBuildAssets && $buildTool !== null) {
        if (is_dir($themePath)) {
            $nodeModulesPath = $themePath . DIRECTORY_SEPARATOR . 'node_modules';

            if (! is_dir($nodeModulesPath)) {
                $installDeps = strtolower($prompt("Install theme dependencies with {$buildTool}? [Y/n]: "));

                if ($installDeps === '' || in_array($installDeps, ['y', 'yes'], true)) {
                    $run(
                        escapeshellarg($buildTool) . ' install --cwd ' . escapeshellarg($themePath),
                        "Installing theme dependencies with {$buildTool}..."
                    );
                }
            }

            $run(
                escapeshellarg($buildTool) . ' run --cwd ' . escapeshellarg($themePath) . ' build',
                "Building theme assets with {$buildTool}..."
            );
        } else {
            fwrite(STDOUT, "Theme not found at {$themePath}. Skipping asset build.\n");
        }
    }
}

$email = '';

if ($skipUser) {
    fwrite(STDOUT, "Skipping admin user creation (--no-user).\n\n");
} else {
    fwrite(
        STDOUT,
        "Create a super admin user.\n" .
        "If you leave the password blank, a secure password will be generated for you.\n\n"
    );
}

if (! $skipUser) {
    while ($email === '') {
        $email = $prompt('Admin email address: ');

        if ($email === '') {
            fwrite(STDOUT, "Email address is required.\n");
        }
    }

    $name = $prompt('Admin display name (default: Admin): ');
    $password = $prompt('Admin password (leave blank to generate): ');

    $artisanCommand = [
        PHP_BINARY,
        $artisanPath,
        'tp:users:make-admin',
        $email,
        '--super',
    ];

    if ($name !== '') {
        $artisanCommand[] = "--name={$name}";
    }

    if ($password !== '') {
        $artisanCommand[] = "--password={$password}";
    }

    $artisanShell = implode(' ', array_map('escapeshellarg', $artisanCommand));

    $run($artisanShell, 'Creating your super admin user...');
}

fwrite(STDOUT, "\nSetup complete.\n");

fwrite(
    STDOUT,
    "\nNext steps:\n" .
    "- If using Laravel Herd, visit https://tentapress.test/admin\n" .
    "- Otherwise run: php artisan serve, then visit the printed URL + /admin\n" .
    "- Log in with the admin email/password you just created.\n"
);
