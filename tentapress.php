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
$verbose = in_array('--verbose', $argv, true);
$quiet = in_array('--quiet', $argv, true);
$assumeDefaults = in_array('--yes', $argv, true) || in_array('--defaults', $argv, true);
$logOverride = null;
$adminEmailOverride = null;
$adminNameOverride = null;
$adminPasswordOverride = null;
$usePasswordStdin = in_array('--password-stdin', $argv, true);

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--log=')) {
        $logOverride = substr($arg, 6);
        break;
    }
}

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--email=')) {
        $adminEmailOverride = trim(substr($arg, 8));
        break;
    }
}

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--name=')) {
        $adminNameOverride = trim(substr($arg, 7));
        break;
    }
}

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--password=')) {
        $adminPasswordOverride = substr($arg, 11);
        break;
    }
}

fwrite(
    STDOUT,
    "TentaPress setup\n" .
    "We'll walk you through a few quick steps.\n\n"
);

if ($command !== 'setup') {
    fwrite(STDOUT, "Usage: php tentapress.php setup [--no-user] [--email=address] [--name=display] [--password=secret|--password-stdin] [--yes] [--verbose] [--quiet] [--log=path]\n");
    exit($command === '' ? 0 : 1);
}

$logPath = $logOverride;

if ($logPath !== null && $logPath !== '' && ! str_starts_with($logPath, DIRECTORY_SEPARATOR)) {
    $logPath = $root . DIRECTORY_SEPARATOR . $logPath;
}

if ($logPath === null || $logPath === '') {
    $logPath = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'setup-' . date('Ymd-His') . '.log';
}

$logDir = dirname($logPath);

if (! is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

if (! is_dir($logDir)) {
    fwrite(STDERR, "Unable to create log directory at {$logDir}.\n");
    exit(1);
}

$logHeader = "Setup started at " . date('c');
file_put_contents($logPath, $logHeader . PHP_EOL);

$writeLog = static function (string $message) use ($logPath): void {
    file_put_contents($logPath, $message . PHP_EOL, FILE_APPEND);
};

$stepStart = static function (string $label) use ($quiet, $writeLog): void {
    $writeLog("== {$label} ==");
    if (! $quiet) {
        fwrite(STDOUT, "- {$label}... ");
    }
};

$stepSuccess = static function () use ($quiet, $writeLog): void {
    $writeLog("Status: OK");
    if (! $quiet) {
        fwrite(STDOUT, "OK\n");
    }
};

$stepFail = static function (string $label, int $status, string $logPath, bool $quiet) use ($writeLog): void {
    $writeLog("Status: FAIL ({$status})");
    if (! $quiet) {
        fwrite(STDOUT, "FAIL\n");
    }
    fwrite(STDERR, "Error: {$label} failed. See log: {$logPath}.\n");
};

$info = static function (string $message) use ($quiet, $writeLog): void {
    $writeLog($message);
    if (! $quiet) {
        fwrite(STDOUT, $message . "\n");
    }
};

if ($usePasswordStdin) {
    if ($adminPasswordOverride !== null) {
        $info('Warning: --password-stdin overrides --password.');
    }

    $stdinPassword = fgets(STDIN);
    $adminPasswordOverride = $stdinPassword === false ? '' : rtrim($stdinPassword, "\r\n");
}

if ($adminPasswordOverride !== null && $adminPasswordOverride !== '' && ! $usePasswordStdin) {
    $info('Warning: --password is visible in shell history. Consider leaving it blank to auto-generate.');
}

$info("Setup log: {$logPath}");

$run = static function (string $shellCommand, string $label) use ($logPath, $quiet, $verbose, $root, $stepStart, $stepSuccess, $stepFail, $writeLog): void {
    $stepStart($label);
    $writeLog('$ ' . $shellCommand);

    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($shellCommand, $descriptorSpec, $pipes, $root);

    if (! is_resource($process)) {
        $stepFail($label, 1, $logPath, $quiet);
        exit(1);
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    if ($stdout !== false && $stdout !== '') {
        $writeLog($stdout);
        if ($verbose && ! $quiet) {
            fwrite(STDOUT, $stdout);
        }
    }

    if ($stderr !== false && $stderr !== '') {
        $writeLog($stderr);
        if ($verbose && ! $quiet) {
            fwrite(STDERR, $stderr);
        }
    }

    $status = proc_close($process);

    if ($status !== 0) {
        $stepFail($label, (int) $status, $logPath, $quiet);
        exit((int) $status);
    }

    $stepSuccess();
};

$runWithOutput = static function (string $shellCommand, string $label, bool $redactPassword) use ($logPath, $quiet, $verbose, $root, $stepStart, $stepSuccess, $stepFail, $writeLog): array {
    $stepStart($label);
    $writeLog('$ ' . $shellCommand);

    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($shellCommand, $descriptorSpec, $pipes, $root);

    if (! is_resource($process)) {
        $stepFail($label, 1, $logPath, $quiet);
        exit(1);
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    if ($redactPassword) {
        if ($stdout !== false) {
            $stdout = (string) preg_replace('/Password:\s*.+/i', 'Password: (hidden)', $stdout);
        }

        if ($stderr !== false) {
            $stderr = (string) preg_replace('/Password:\s*.+/i', 'Password: (hidden)', $stderr);
        }
    }

    if ($stdout !== false && $stdout !== '') {
        $writeLog($stdout);
        if ($verbose && ! $quiet) {
            fwrite(STDOUT, $stdout);
        }
    }

    if ($stderr !== false && $stderr !== '') {
        $writeLog($stderr);
        if ($verbose && ! $quiet) {
            fwrite(STDERR, $stderr);
        }
    }

    $status = proc_close($process);

    if ($status !== 0) {
        $stepFail($label, (int) $status, $logPath, $quiet);
        exit((int) $status);
    }

    $stepSuccess();

    return [
        'stdout' => $stdout === false ? '' : $stdout,
        'stderr' => $stderr === false ? '' : $stderr,
    ];
};

$prompt = static function (string $label): string {
    fwrite(STDOUT, $label);

    $line = fgets(STDIN);

    return $line === false ? '' : trim($line);
};

$promptChoice = static function (string $label, array $choices, string $default) use ($prompt, $assumeDefaults): string {
    if ($assumeDefaults) {
        return $default;
    }
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

$confirm = static function (string $label, bool $defaultYes) use ($prompt, $assumeDefaults): bool {
    if ($assumeDefaults) {
        return $defaultYes;
    }

    $choice = strtolower($prompt($label));

    if ($choice === '') {
        return $defaultYes;
    }

    return in_array($choice, ['y', 'yes'], true);
};

$ensureEnvFile = static function () use ($root, $run, $info): ?string {
    $envFile = $root . '/.env';
    $exampleEnvFile = $root . '/.env.example';

    if (! file_exists($envFile) && file_exists($exampleEnvFile)) {
        $run(
            escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg("file_exists('.env') || copy('.env.example', '.env');"),
            'Preparing environment (.env)'
        );
    }

    if (! file_exists($envFile)) {
        $info('Warning: no .env file found and .env.example is missing. Skipping environment configuration.');
        return null;
    }

    return $envFile;
};

$readEnvValue = static function (string $envContent, string $key): ?string {
    $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';
    if (! preg_match($pattern, $envContent, $matches)) {
        return null;
    }

    $rawValue = trim($matches[1]);
    if ($rawValue === '') {
        return '';
    }

    if (
        strlen($rawValue) >= 2
        && (($rawValue[0] === '"' && $rawValue[strlen($rawValue) - 1] === '"')
            || ($rawValue[0] === "'" && $rawValue[strlen($rawValue) - 1] === "'"))
    ) {
        return substr($rawValue, 1, -1);
    }

    return $rawValue;
};

$formatEnvValue = static function (string $value): string {
    if ($value === '') {
        return '""';
    }

    if (preg_match('/^[A-Za-z0-9._:\/-]+$/', $value)) {
        return $value;
    }

    return '"' . addcslashes($value, "\\\"") . '"';
};

$upsertEnvValue = static function (string &$envContent, string $key, string $value) use ($formatEnvValue): void {
    $formattedValue = $formatEnvValue($value);
    $replacement = $key . '=' . $formattedValue;
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

    if (preg_match($pattern, $envContent) === 1) {
        $envContent = (string) preg_replace($pattern, $replacement, $envContent, 1);
        return;
    }

    $envContent = rtrim($envContent) . PHP_EOL . $replacement . PHP_EOL;
};

$copyThemeFromVendor = static function (string $packageName) use ($prompt, $resolvePackagePath, $root): array|string|null {
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
        $relative = ltrim(str_replace($installPath . DIRECTORY_SEPARATOR, '', $item->getPathname()), DIRECTORY_SEPARATOR);
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

$envFile = $ensureEnvFile();
if ($envFile !== null) {
    $envContent = file_get_contents($envFile);

    if (! is_string($envContent)) {
        fwrite(STDERR, "Unable to read {$envFile}.\n");
        exit(1);
    }

    $existingAppName = $readEnvValue($envContent, 'APP_NAME') ?? 'TentaPress';
    $existingAppUrl = $readEnvValue($envContent, 'APP_URL') ?? 'http://localhost';

    if ($assumeDefaults) {
        $appName = $existingAppName;
        $appUrl = $existingAppUrl;
    } else {
        $appNameInput = $prompt("Application name [{$existingAppName}]: ");
        $appUrlInput = $prompt("Application URL [{$existingAppUrl}]: ");

        $appName = $appNameInput === '' ? $existingAppName : $appNameInput;
        $appUrl = $appUrlInput === '' ? $existingAppUrl : $appUrlInput;
    }

    $upsertEnvValue($envContent, 'APP_NAME', $appName);
    $upsertEnvValue($envContent, 'APP_URL', $appUrl);
    $upsertEnvValue($envContent, 'APP_ENV', 'production');
    $upsertEnvValue($envContent, 'APP_DEBUG', 'false');

    file_put_contents($envFile, $envContent);
    $info('Configured .env values for APP_NAME, APP_URL, APP_ENV=production, APP_DEBUG=false.');
}

if (! $hasComposerLock && ! $hasVendorFolder) {
    $sqlitePath = $root . '/database/database.sqlite';

    if (! file_exists($sqlitePath)) {
        $run(
            escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg("file_exists('database/database.sqlite') || touch('database/database.sqlite');"),
            'Preparing database (SQLite)'
        );
    }

    $run(
        $composerCommand . ' install --no-dev --no-interaction --optimize-autoloader --no-scripts',
        'Installing dependencies'
    );

    $run($composerCommand . ' run post-autoload-dump', 'Initializing app (Composer scripts)');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' key:generate', 'Initializing app (app key)');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' migrate --force', 'Initializing app (database)');
    $run($composerCommand . ' run post-update-cmd', 'Initializing app (post-update scripts)');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' storage:link', 'Linking storage');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:plugins defaults --no-interaction', 'Activating default plugins');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' migrate --force', 'Finalizing database');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:plugins enable --all', 'Enabling plugins');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:permissions seed', 'Seeding permissions');
} else {
    $info(
        "Detected existing install files (composer.lock or vendor/ present).\n" .
        "Skipping dependency install and core setup steps."
    );
}

$themeChoice = $promptChoice(
    "Install a starter theme? [tailwind/none] (default: tailwind): ",
    [
        'tailwind' => 'tentaplane/theme-tailwind',
        'none' => 'none',
    ],
    'tentaplane/theme-tailwind'
);

if ($themeChoice !== 'none') {
    $run(
        $composerCommand . ' require ' . escapeshellarg($themeChoice) . ' --no-interaction --no-scripts',
        "Installing theme package"
    );
    $themeCopy = $copyThemeFromVendor($themeChoice);
    $themeId = is_array($themeCopy) ? (string) ($themeCopy['id'] ?? $themeChoice) : $themeChoice;
    $themeInstallPath = is_array($themeCopy) ? ($themeCopy['install_path'] ?? null) : null;
    $themePath = $root . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $themeId);
    $buildTool = null;
    $shouldBuildAssets = false;
    $run($composerCommand . ' run post-autoload-dump', 'Refreshing Composer autoload');
    $run($composerCommand . ' run post-update-cmd', 'Finalizing theme install');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes sync', 'Syncing themes');
    $run(
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes activate ' . escapeshellarg($themeId),
        "Activating theme"
    );

    $buildAssets = $confirm('Build theme assets now? [Y/n]: ', true);

    if ($buildAssets) {
        $shouldBuildAssets = true;
        $availableTools = $resolveCommands(['bun', 'pnpm', 'npm']);

        if ($availableTools === []) {
            $info('No bun/pnpm/npm detected. Skipping theme asset build.');
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

    $seedDemo = $confirm('Create a demo homepage with sample blocks? [Y/n]: ', true);

    if ($seedDemo) {
        $sourceBlocksPath = $root
            . DIRECTORY_SEPARATOR
            . 'vendor'
            . DIRECTORY_SEPARATOR
            . 'tentapress'
            . DIRECTORY_SEPARATOR
            . 'blocks'
            . DIRECTORY_SEPARATOR
            . 'resources'
            . DIRECTORY_SEPARATOR
            . 'views'
            . DIRECTORY_SEPARATOR
            . 'blocks';
        $targetBlocksPath = $themePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'blocks';
        $demoBlockViews = [
            'hero',
            'hero/default',
            'hero/split',
            'logo-cloud',
            'features',
            'content',
            'stats',
            'quote',
            'testimonial',
            'gallery',
            'image',
            'timeline',
            'table',
            'faq',
            'newsletter',
            'embed',
            'map',
            'divider',
            'cta',
        ];

        if (is_dir($sourceBlocksPath)) {
            if (! is_dir($targetBlocksPath)) {
                mkdir($targetBlocksPath, 0755, true);
            }

            foreach ($demoBlockViews as $blockView) {
                $relativeView = str_replace('/', DIRECTORY_SEPARATOR, $blockView) . '.blade.php';
                $sourceFile = $sourceBlocksPath . DIRECTORY_SEPARATOR . $relativeView;
                $targetFile = $targetBlocksPath . DIRECTORY_SEPARATOR . $relativeView;

                if (is_file($sourceFile) && ! is_file($targetFile)) {
                    $targetDir = dirname($targetFile);

                    if (! is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }

                    copy($sourceFile, $targetFile);
                }
            }
        }

        $demoBlocks = [
            [
                'type' => 'blocks/hero',
                'variant' => 'default',
                'props' => [
                    'eyebrow' => 'TentaPress for agencies',
                    'headline' => 'Launch client sites in days, not weeks.',
                    'subheadline' => 'A modern block editor with structured content, fast previews, and clean deployment.',
                    'alignment' => 'left',
                    'image_position' => 'right',
                    'background_image' => 'https://placehold.co/720x520?text=Hero+Preview',
                    'actions' => [
                        [
                            'label' => 'Open admin',
                            'url' => '/admin',
                            'style' => 'primary',
                        ],
                        [
                            'label' => 'View docs',
                            'url' => 'https://tentapress.com/docs',
                            'style' => 'outline',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'blocks/logo-cloud',
                'props' => [
                    'title' => 'Trusted by modern studios',
                    'subtitle' => 'Placeholder client logos for your next launch.',
                    'logos' => [
                        'https://placehold.co/160x48?text=Studio+1',
                        'https://placehold.co/160x48?text=Studio+2',
                        'https://placehold.co/160x48?text=Studio+3',
                        'https://placehold.co/160x48?text=Studio+4',
                        'https://placehold.co/160x48?text=Studio+5',
                    ],
                    'columns' => '5',
                    'grayscale' => true,
                    'size' => 'md',
                ],
            ],
            [
                'type' => 'blocks/features',
                'props' => [
                    'title' => 'Everything you need to launch',
                    'subtitle' => 'Compose pages fast with clean blocks, predictable layouts, and agency-ready workflows.',
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
                'type' => 'blocks/content',
                'props' => [
                    'content' => "Add rich, structured content without losing control of the layout.\n\nUse blocks to keep your pages consistent and easy to evolve. This placeholder copy is here to show the flow.",
                    'width' => 'normal',
                    'alignment' => 'left',
                    'background' => 'muted',
                ],
            ],
            [
                'type' => 'blocks/stats',
                'props' => [
                    'title' => 'By the numbers',
                    'items' => [
                        ['value' => '10×', 'label' => 'Faster publishing'],
                        ['value' => '4 days', 'label' => 'Average turnaround'],
                        ['value' => '24/7', 'label' => 'Client-ready uptime'],
                    ],
                    'columns' => '3',
                    'dividers' => true,
                ],
            ],
            [
                'type' => 'blocks/quote',
                'props' => [
                    'quote' => 'TentaPress gives our team one place to build, edit, and ship every client site.',
                    'name' => 'Jordan Lee',
                    'role' => 'Creative Director, Demo Agency',
                    'alignment' => 'left',
                    'style' => 'simple',
                ],
            ],
            [
                'type' => 'blocks/testimonial',
                'props' => [
                    'quote' => 'We replaced three tools with TentaPress and shipped a full site in a single day.',
                    'name' => 'Taylor Rivers',
                    'role' => 'Head of Growth, Demo Studio',
                    'rating' => 5,
                    'alignment' => 'left',
                    'style' => 'card',
                ],
            ],
            [
                'type' => 'blocks/gallery',
                'props' => [
                    'images' => [
                        'https://placehold.co/640x480?text=Gallery+01',
                        'https://placehold.co/640x480?text=Gallery+02',
                        'https://placehold.co/640x480?text=Gallery+03',
                        'https://placehold.co/640x480?text=Gallery+04',
                        'https://placehold.co/640x480?text=Gallery+05',
                        'https://placehold.co/640x480?text=Gallery+06',
                    ],
                    'columns' => '3',
                    'gap' => 'md',
                    'aspect' => '4:3',
                    'rounded' => true,
                ],
            ],
            [
                'type' => 'blocks/image',
                'props' => [
                    'image' => 'https://placehold.co/960x540?text=Case+Study+Hero',
                    'alt' => 'Placeholder project preview',
                    'caption' => 'Placeholder project hero for a client launch.',
                    'link' => [
                        'url' => '#',
                        'label' => 'View case study',
                    ],
                    'alignment' => 'center',
                    'width' => 'wide',
                    'rounded' => true,
                    'shadow' => true,
                ],
            ],
            [
                'type' => 'blocks/timeline',
                'props' => [
                    'title' => 'Launch timeline',
                    'items' => [
                        ['date' => 'Week 1', 'title' => 'Discovery', 'body' => 'Scope, content, and site map.'],
                        ['date' => 'Week 2', 'title' => 'Build', 'body' => 'Compose pages and approve copy.'],
                        ['date' => 'Week 3', 'title' => 'Launch', 'body' => 'Go live with hosting and analytics.'],
                    ],
                ],
            ],
            [
                'type' => 'blocks/table',
                'props' => [
                    'title' => 'Sample pricing',
                    'data' => "Plan,Price,Pages\nStarter,$49,3\nAgency,$149,10\nStudio,$299,Unlimited",
                    'striped' => true,
                ],
            ],
            [
                'type' => 'blocks/faq',
                'props' => [
                    'title' => 'Frequently asked',
                    'subtitle' => 'Answers to common questions from agencies.',
                    'items' => [
                        ['question' => 'Can we customize the theme?', 'answer' => 'Yes, copy the theme into your installation and adjust the blocks.'],
                        ['question' => 'Do clients need training?', 'answer' => 'No, the block editor keeps content structured and predictable.'],
                        ['question' => 'Is hosting included?', 'answer' => 'Connect your preferred hosting provider and deploy anywhere.'],
                    ],
                    'open_first' => true,
                ],
            ],
            [
                'type' => 'blocks/embed',
                'props' => [
                    'title' => 'Product walkthrough',
                    'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    'aspect' => '16:9',
                    'height' => 480,
                    'allow_fullscreen' => true,
                    'caption' => 'Placeholder video embed.',
                ],
            ],
            [
                'type' => 'blocks/newsletter',
                'props' => [
                    'title' => 'Keep the launch list updated',
                    'body' => 'Monthly product notes and agency tips.',
                    'actions' => [
                        ['label' => 'Subscribe', 'url' => '#', 'style' => 'primary'],
                    ],
                    'email_placeholder' => 'hello@agency.com',
                    'disclaimer' => 'Placeholder only. Unsubscribe anytime.',
                    'alignment' => 'left',
                ],
            ],
            [
                'type' => 'blocks/map',
                'props' => [
                    'title' => 'Where we work',
                    'embed_url' => 'https://maps.google.com/maps?q=New%20York&output=embed',
                    'height' => 420,
                    'caption' => 'Placeholder map embed.',
                    'border' => true,
                ],
            ],
            [
                'type' => 'blocks/divider',
                'props' => [
                    'height' => 32,
                    'label' => '',
                    'style' => 'line',
                ],
            ],
            [
                'type' => 'blocks/cta',
                'props' => [
                    'title' => 'Ready to build your next launch?',
                    'body' => 'Start with the base theme and tailor it to your clients.',
                    'alignment' => 'left',
                    'background' => 'muted',
                    'actions' => [
                        [
                            'label' => 'Open admin',
                            'url' => '/admin',
                            'style' => 'primary',
                        ],
                        [
                            'label' => 'Explore docs',
                            'url' => 'https://tentapress.com/docs',
                            'style' => 'outline',
                        ],
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
            'Creating demo homepage'
        );
    }

    $demoTitle = 'TentaPress Demo Install';
    $demoTagline = 'Build with clarity. Publish with confidence.';
    $demoTitleExport = var_export($demoTitle, true);
    $demoTaglineExport = var_export($demoTagline, true);
    $settingsScript = <<<PHP
require __DIR__ . '/vendor/autoload.php';

\$app = require __DIR__ . '/bootstrap/app.php';
\$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;
use TentaPress\\Pages\\Models\\TpPage;

if (!Schema::hasTable('tp_settings')) {
    return;
}

\$now = now();
\$upsert = static function (string \$key, string \$value, bool \$autoload) use (\$now): void {
    DB::table('tp_settings')->updateOrInsert(
        ['key' => \$key],
        [
            'value' => \$value,
            'autoload' => \$autoload,
            'created_at' => \$now,
            'updated_at' => \$now,
        ]
    );
};

\$upsert('site.title', {$demoTitleExport}, true);
\$upsert('site.tagline', {$demoTaglineExport}, true);
PHP;

    $run(
        escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($settingsScript),
        'Populating demo settings'
    );

    if ($shouldBuildAssets && $buildTool !== null) {
        if (is_dir($themePath)) {
            $nodeModulesPath = $themePath . DIRECTORY_SEPARATOR . 'node_modules';

            if (! is_dir($nodeModulesPath)) {
                $installDeps = $confirm("Install theme dependencies with {$buildTool}? [Y/n]: ", true);

                if ($installDeps) {
                    $installCommand = match ($buildTool) {
                        'npm' => escapeshellarg($buildTool) . ' install --prefix ' . escapeshellarg($themePath),
                        'pnpm' => escapeshellarg($buildTool) . ' install --dir ' . escapeshellarg($themePath),
                        default => escapeshellarg($buildTool) . ' install --cwd ' . escapeshellarg($themePath),
                    };

                    $run(
                        $installCommand,
                        "Installing theme dependencies"
                    );
                }
            }

            $buildCommand = match ($buildTool) {
                'npm' => escapeshellarg($buildTool) . ' --prefix ' . escapeshellarg($themePath) . ' run build',
                'pnpm' => escapeshellarg($buildTool) . ' --dir ' . escapeshellarg($themePath) . ' run build',
                default => escapeshellarg($buildTool) . ' run --cwd ' . escapeshellarg($themePath) . ' build',
            };

            $run(
                $buildCommand,
                "Building theme assets"
            );
        } else {
            $info("Theme not found at {$themePath}. Skipping asset build.");
        }
    }
}

$email = $adminEmailOverride ?? '';

if ($skipUser) {
    $info('Skipping admin user creation (--no-user).');
} else {
    fwrite(
        STDOUT,
        "Create your admin login.\n" .
        "Leave the password blank to generate a secure one.\n\n"
    );
}

if (! $skipUser) {
    if ($email === '') {
        while ($email === '') {
            $email = $prompt('Admin email address: ');

            if ($email === '') {
                fwrite(STDOUT, "Email address is required.\n");
            }
        }
    }

    $name = $adminNameOverride ?? $prompt('Admin display name (default: Admin): ');
    $password = $adminPasswordOverride ?? $prompt('Admin password (leave blank to generate): ');
    $passwordHidden = $password !== '';

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

    $result = $runWithOutput($artisanShell, 'Creating admin user', $passwordHidden);

    $info('Admin user created:');
    $info("- Email: {$email}");
    $info('- Name: ' . ($name !== '' ? $name : 'Admin'));

    if (! $passwordHidden) {
        $output = $result['stdout'] . "\n" . $result['stderr'];
        $generatedPassword = '';

        if (preg_match('/Password:\s*(.+)/', $output, $matches)) {
            $generatedPassword = trim($matches[1]);
        }

        if ($generatedPassword !== '') {
            $info("- Password: {$generatedPassword}");
        } else {
            $info('- Password: (see setup log)');
        }
    }
}

fwrite(STDOUT, "\nSetup complete.\n");

fwrite(
    STDOUT,
    "\nNext steps:\n" .
    "- If using Laravel Herd, visit https://yourdomain.test/admin\n" .
    "- Otherwise run: php artisan serve, then visit the printed URL + /admin\n" .
    "- Log in with the admin email and the password you set (or generated).\n"
);
