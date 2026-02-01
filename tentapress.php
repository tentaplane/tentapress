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
    fwrite(STDERR, "Unable to find artisan at {$artisanPath}. Run this from the repo root.\n");
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

$copyThemeFromVendor = static function (string $packageName) use ($prompt, $resolvePackagePath, $root): ?string {
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

    return $themeId;
};

$resolveCommand = static function (array $candidates): ?string {
    foreach ($candidates as $candidate) {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null'));
        if ($path !== '') {
            return $candidate;
        }
    }

    return null;
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
    $themeId = $copyThemeFromVendor($themeChoice) ?? $themeChoice;
    $run($composerCommand . ' run post-autoload-dump', 'Running Composer post-autoload-dump scripts...');
    $run($composerCommand . ' run post-update-cmd', 'Running Composer post-update-cmd scripts...');
    $run(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes sync', 'Syncing themes...');
    $run(
        escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisanPath) . ' tp:themes activate ' . escapeshellarg($themeId),
        "Activating theme {$themeId}..."
    );

    $buildAssets = strtolower($prompt('Build theme assets now? [y/N]: '));
    if (in_array($buildAssets, ['y', 'yes'], true)) {
        $buildTool = $resolveCommand(['bun', 'pnpm', 'npm']);
        if ($buildTool === null) {
            fwrite(STDOUT, "No bun/pnpm/npm detected. Skipping theme asset build.\n");
        } else {
            $themePath = $root . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $themeId);
            if (is_dir($themePath)) {
                $run(
                    escapeshellarg($buildTool) . ' run --cwd ' . escapeshellarg($themePath) . ' build',
                    "Building theme assets with {$buildTool}..."
                );
            } else {
                fwrite(STDOUT, "Theme path not found at {$themePath}. Skipping asset build.\n");
            }
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
