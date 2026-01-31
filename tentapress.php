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
