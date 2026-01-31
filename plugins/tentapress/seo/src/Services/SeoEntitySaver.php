<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class SeoEntitySaver
{
    /**
     * @param class-string<Model> $modelClass
     */
    public function syncFromRequest(
        int $entityId,
        string $table,
        string $foreignKey,
        string $modelClass,
        Request $request
    ): void {
        if ($entityId <= 0) {
            return;
        }

        if (!Schema::hasTable($table)) {
            return;
        }

        $payload = [
            $foreignKey => $entityId,
            'title' => $this->nullIfEmpty($request->input('seo_title')),
            'description' => $this->nullIfEmpty($request->input('seo_description')),
            'robots' => $this->nullIfEmpty($request->input('seo_robots')),
            'canonical_url' => $this->nullIfEmpty($request->input('seo_canonical_url')),
            'og_image' => $this->nullIfEmpty($request->input('seo_og_image')),
            'twitter_image' => $this->nullIfEmpty($request->input('seo_twitter_image')),
        ];

        if (!$this->requestHadAnySeoFields($request)) {
            return;
        }

        if ($this->allEmpty($payload)) {
            $modelClass::query()->where($foreignKey, $entityId)->delete();
            return;
        }

        $modelClass::query()->updateOrCreate(
            [$foreignKey => $entityId],
            $payload
        );
    }

    private function requestHadAnySeoFields(Request $request): bool
    {
        $keys = [
            'seo_title',
            'seo_description',
            'seo_robots',
            'seo_canonical_url',
            'seo_og_image',
            'seo_twitter_image',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                return true;
            }
        }

        return false;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function allEmpty(array $payload): bool
    {
        foreach (['title', 'description', 'robots', 'canonical_url', 'og_image', 'twitter_image'] as $key) {
            $value = $payload[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
