<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;

final class WorkflowResourceResolver
{
    public function resolveModel(string $resourceType, int $resourceId): ?Model
    {
        $resourceType = $this->normalizeType($resourceType);

        if ($resourceType === 'pages') {
            if (! class_exists(TpPage::class) || ! Schema::hasTable('tp_pages')) {
                return null;
            }

            return TpPage::query()->find($resourceId);
        }

        if ($resourceType === 'posts') {
            if (! class_exists(TpPost::class) || ! Schema::hasTable('tp_posts')) {
                return null;
            }

            return TpPost::query()->find($resourceId);
        }

        return null;
    }

    public function editUrl(string $resourceType, int $resourceId): string
    {
        $resourceType = $this->normalizeType($resourceType);

        if ($resourceType === 'pages' && Route::has('tp.pages.edit')) {
            return route('tp.pages.edit', ['page' => $resourceId]);
        }

        if ($resourceType === 'posts' && Route::has('tp.posts.edit')) {
            return route('tp.posts.edit', ['post' => $resourceId]);
        }

        return '/admin';
    }

    public function capabilityFor(string $resourceType): string
    {
        return match ($this->normalizeType($resourceType)) {
            'pages' => 'manage_pages',
            'posts' => 'manage_posts',
            default => throw new InvalidArgumentException('Unsupported workflow resource.'),
        };
    }

    public function labelFor(string $resourceType): string
    {
        return match ($this->normalizeType($resourceType)) {
            'pages' => 'Page',
            'posts' => 'Post',
            default => 'Item',
        };
    }

    public function normalizeType(string $resourceType): string
    {
        return trim(strtolower($resourceType));
    }
}
