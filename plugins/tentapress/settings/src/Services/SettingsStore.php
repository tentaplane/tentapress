<?php

declare(strict_types=1);

namespace TentaPress\Settings\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Settings\Models\TpSetting;

final class SettingsStore
{
    private ?array $cache = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $this->warm();

        if ($this->cache !== null && array_key_exists($key, $this->cache)) {
            $val = $this->cache[$key];

            return $val ?? $default;
        }

        return $default;
    }

    public function set(string $key, mixed $value, bool $autoload = true): void
    {
        $valueStr = $value === null ? null : (string) $value;

        TpSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $valueStr, 'autoload' => $autoload]
        );

        // Update request cache
        if ($this->cache === null) {
            $this->cache = [];
        }
        $this->cache[$key] = $valueStr;
    }

    public function forget(string $key): void
    {
        TpSetting::query()->where('key', $key)->delete();

        if ($this->cache !== null) {
            unset($this->cache[$key]);
        }
    }

    /**
     * @return array<string,string|null>
     */
    public function all(): array
    {
        $this->warm();

        return $this->cache ?? [];
    }

    private function warm(): void
    {
        if ($this->cache !== null) {
            return;
        }

        if (!Schema::hasTable('tp_settings')) {
            $this->cache = [];
            return;
        }

        // autoload only for v0 simplicity
        $rows = DB::table('tp_settings')->where('autoload', true)->get(['key', 'value']);

        $out = [];
        foreach ($rows as $r) {
            $k = (string) ($r->key ?? '');
            if ($k === '') {
                continue;
            }
            $out[$k] = isset($r->value) ? (string) $r->value : null;
        }

        $this->cache = $out;
    }
}
