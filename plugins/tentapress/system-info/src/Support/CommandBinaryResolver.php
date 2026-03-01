<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Support;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

final class CommandBinaryResolver
{
    public function phpCliBinary(): string
    {
        $finder = new ExecutableFinder();

        $configuredBinary = trim((string) config('tentapress-system-info.plugin_lifecycle.php_binary', ''));
        if ($this->isUsablePhpCliBinary($configuredBinary)) {
            return $configuredBinary;
        }

        if ($this->isUsablePhpCliBinary(PHP_BINARY)) {
            return PHP_BINARY;
        }

        $phpBindirBinary = rtrim(PHP_BINDIR, '/') . '/php';
        if ($this->isUsablePhpCliBinary($phpBindirBinary)) {
            return $phpBindirBinary;
        }

        $commonPhpPaths = [
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/opt/homebrew/bin/php',
            '/usr/bin/php8.4',
            '/usr/bin/php8.3',
            '/usr/bin/php8.2',
        ];

        foreach ($commonPhpPaths as $phpPath) {
            if ($this->isUsablePhpCliBinary($phpPath)) {
                return $phpPath;
            }
        }

        $php = $finder->find('php');
        if ($this->isUsablePhpCliBinary($php)) {
            return (string) $php;
        }

        throw new RuntimeException('PHP CLI binary not found. TentaPress could not detect a usable php CLI binary automatically.');
    }

    /**
     * @return array<int,string>
     */
    public function composerBaseCommand(string $phpBinary): array
    {
        $finder = new ExecutableFinder();
        $projectComposer = base_path('composer.phar');
        if (is_file($projectComposer)) {
            return [$phpBinary, $projectComposer];
        }

        $configuredBinary = trim((string) config('tentapress-system-info.plugin_lifecycle.composer_binary', ''));
        if ($configuredBinary !== '' && is_file($configuredBinary) && is_executable($configuredBinary)) {
            return [$configuredBinary];
        }

        $commonComposerPaths = [
            '/usr/local/bin/composer',
            '/opt/homebrew/bin/composer',
            '/usr/bin/composer',
        ];

        foreach ($commonComposerPaths as $composerPath) {
            if (is_file($composerPath) && is_executable($composerPath)) {
                return [$composerPath];
            }
        }

        $composer = $finder->find('composer');
        if (is_string($composer) && $composer !== '') {
            return [$composer];
        }

        throw new RuntimeException('Composer binary not found. TentaPress could not detect a usable Composer binary automatically.');
    }

    private function isUsablePhpCliBinary(?string $binary): bool
    {
        if (! is_string($binary) || $binary === '' || ! is_file($binary) || ! is_executable($binary)) {
            return false;
        }

        $name = strtolower(basename($binary));

        return ! str_contains($name, 'fpm');
    }
}
