<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use TentaPress\Settings\Services\SettingsStore;

final class ContentTypeBasePathValidator
{
    /**
     * @var array<int,string>
     */
    private array $reserved = [
        'admin',
        'api',
        'assets',
        'build',
        'login',
        'logout',
        'storage',
        'up',
        'vendor',
    ];

    public function normalize(string $value): string
    {
        $basePath = Str::slug($value);
        $basePath = trim($basePath);

        throw_if($basePath === '', RuntimeException::class, 'The base path must not be empty.');
        throw_if(! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $basePath), RuntimeException::class, 'The base path must use lowercase kebab-case.');
        throw_if(in_array($basePath, $this->reservedPaths(), true), RuntimeException::class, "The base path '{$basePath}' is reserved.");

        return $basePath;
    }

    public function assertAvailable(string $value, ?int $ignoreContentTypeId = null): string
    {
        $basePath = $this->normalize($value);

        if (Schema::hasTable('tp_content_types')) {
            $query = DB::table('tp_content_types')->where('base_path', $basePath);

            if ($ignoreContentTypeId !== null) {
                $query->where('id', '!=', $ignoreContentTypeId);
            }

            throw_if($query->exists(), RuntimeException::class, "The base path '{$basePath}' is already in use.");
        }

        if (Schema::hasTable('tp_pages')) {
            throw_if(
                DB::table('tp_pages')->where('slug', $basePath)->exists(),
                RuntimeException::class,
                "The base path '{$basePath}' conflicts with an existing page slug."
            );
        }

        return $basePath;
    }

    /**
     * @return array<int,string>
     */
    public function reservedPaths(): array
    {
        $paths = $this->reserved;

        if (class_exists(SettingsStore::class) && app()->bound(SettingsStore::class)) {
            $blogBase = trim((string) resolve(SettingsStore::class)->get('site.blog_base', ''), '/');

            if ($blogBase !== '') {
                $paths[] = $blogBase;
            }
        }

        return array_values(array_unique($paths));
    }
}
