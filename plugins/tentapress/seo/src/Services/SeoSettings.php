<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SeoSettings
{
    private ?array $cache = null;

    public function titleTemplate(): string
    {
        return (string) $this->get('seo.title_template', '{{page_title}} · {{site_title}}');
    }

    public function defaultDescription(): string
    {
        return (string) $this->get('seo.default_description', '');
    }

    public function defaultRobots(): string
    {
        return (string) $this->get('seo.default_robots', 'index,follow');
    }

    public function canonicalBase(): string
    {
        return rtrim((string) $this->get('seo.canonical_base', ''), '/');
    }

    public function blogTitle(): string
    {
        return (string) $this->get('seo.blog_title', 'Blog');
    }

    public function blogDescription(): string
    {
        return (string) $this->get('seo.blog_description', '');
    }

    public function blogBase(): string
    {
        $base = (string) $this->get('site.blog_base', 'blog');
        $base = trim($base, '/');

        return $base !== '' ? $base : 'blog';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!Schema::hasTable('tp_settings')) {
            return $default;
        }

        $this->warm();

        if ($this->cache !== null && array_key_exists($key, $this->cache)) {
            $v = $this->cache[$key];
            return $v ?? $default;
        }

        return $default;
    }

    public function set(string $key, string $value): void
    {
        if (!Schema::hasTable('tp_settings')) {
            return;
        }

        DB::table('tp_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $value,
                'autoload' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if ($this->cache === null) {
            $this->cache = [];
        }
        $this->cache[$key] = $value;
    }

    private function warm(): void
    {
        if ($this->cache !== null) {
            return;
        }

        $rows = DB::table('tp_settings')
                  ->where('autoload', true)
                  ->whereIn('key', [
                      'seo.title_template',
                      'seo.default_description',
                      'seo.default_robots',
                      'seo.canonical_base',
                      'seo.blog_title',
                      'seo.blog_description',
                      'site.blog_base',
                      'site.title',
                  ])
                  ->get(['key', 'value']);

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
