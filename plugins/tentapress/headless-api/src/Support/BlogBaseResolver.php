<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Support;

use TentaPress\Settings\Services\SettingsStore;

final class BlogBaseResolver
{
    public function fromSettings(SettingsStore $settings): string
    {
        return $this->normalize($settings->get('site.blog_base', 'blog'));
    }

    public function normalize(mixed $value): string
    {
        $blogBase = trim((string) $value, '/');

        if ($blogBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $blogBase) === 1) {
            return $blogBase;
        }

        return 'blog';
    }
}
