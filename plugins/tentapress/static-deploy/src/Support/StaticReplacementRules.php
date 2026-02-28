<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TentaPress\Settings\Services\SettingsStore;

final class StaticReplacementRules
{
    private const SETTINGS_KEY = 'static_deploy.find_replace_rules';

    /**
     * @var array<int,string>
     */
    private const DEFAULT_FILE_PATTERNS = [
        '*.html',
        '*.xml',
        '*.txt',
        '*.css',
        '*.js',
        '*.json',
    ];

    public function canPersist(): bool
    {
        return class_exists(SettingsStore::class) && app()->bound(SettingsStore::class);
    }

    public function savedJson(): string
    {
        if (! $this->canPersist()) {
            return $this->emptyStateJson();
        }

        $raw = resolve(SettingsStore::class)->get(self::SETTINGS_KEY, $this->emptyStateJson());

        return is_string($raw) && trim($raw) !== '' ? $raw : $this->emptyStateJson();
    }

    /**
     * @param array<int,string> $warnings
     * @return array<int,array{find:string,replace:string,files:array<int,string>}>
     */
    public function savedRules(array &$warnings = []): array
    {
        if (! $this->canPersist()) {
            return [];
        }

        try {
            return self::normalize($this->savedJson());
        } catch (\InvalidArgumentException $exception) {
            $warnings[] = 'Saved find/replace rules are invalid and were skipped: ' . $exception->getMessage();

            return [];
        }
    }

    public function save(string $json): void
    {
        if (! $this->canPersist()) {
            return;
        }

        $normalized = self::normalize($json);

        resolve(SettingsStore::class)->set(
            self::SETTINGS_KEY,
            self::encode($normalized),
            true
        );
    }

    public function exampleJson(): string
    {
        return self::encode([
            [
                'find' => '<html',
                'replace' => '<html data-static-export="1"',
                'files' => [
                    '*.html',
                ],
            ],
            [
                'find' => 'https://example.com',
                'replace' => 'https://cdn.example.com',
                'files' => [
                    '*.html',
                    'sitemap.xml',
                ],
            ],
        ]);
    }

    public function emptyStateJson(): string
    {
        return self::encode([]);
    }

    /**
     * @param string $json
     * @return array<int,array{find:string,replace:string,files:array<int,string>}>
     */
    public static function normalize(string $json): array
    {
        $payload = trim($json);

        if ($payload === '') {
            return [];
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException('JSON could not be parsed.');
        }

        if (! is_array($decoded) || ! Arr::isList($decoded)) {
            throw new \InvalidArgumentException('Rules must be a JSON array.');
        }

        $rules = [];

        foreach ($decoded as $index => $rule) {
            if (! is_array($rule)) {
                throw new \InvalidArgumentException('Rule #' . ($index + 1) . ' must be an object.');
            }

            $find = trim((string) ($rule['find'] ?? ''));

            if ($find === '') {
                throw new \InvalidArgumentException('Rule #' . ($index + 1) . ' requires a non-empty find value.');
            }

            if (! array_key_exists('replace', $rule) || ! is_string($rule['replace'])) {
                throw new \InvalidArgumentException('Rule #' . ($index + 1) . ' requires a string replace value.');
            }

            $patterns = self::normalizePatterns($rule['files'] ?? null, $index);

            $rules[] = [
                'find' => $find,
                'replace' => $rule['replace'],
                'files' => $patterns,
            ];
        }

        return $rules;
    }

    /**
     * @param mixed $value
     * @return array<int,string>
     */
    private static function normalizePatterns(mixed $value, int $index): array
    {
        if ($value === null) {
            return self::DEFAULT_FILE_PATTERNS;
        }

        if (! is_array($value) || ! Arr::isList($value)) {
            throw new \InvalidArgumentException('Rule #' . ($index + 1) . ' files must be an array of glob strings.');
        }

        $patterns = [];

        foreach ($value as $pattern) {
            $normalized = trim((string) $pattern);

            if ($normalized === '') {
                continue;
            }

            $patterns[] = Str::replace('\\', '/', $normalized);
        }

        if ($patterns === []) {
            return self::DEFAULT_FILE_PATTERNS;
        }

        return array_values(array_unique($patterns));
    }

    /**
     * @param array<int,array{find:string,replace:string,files:array<int,string>}> $rules
     */
    private static function encode(array $rules): string
    {
        return json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
