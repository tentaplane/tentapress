<?php

declare(strict_types=1);

namespace TentaPress\Media\Contracts;

use TentaPress\Media\Models\TpMedia;

interface MediaUrlGenerator
{
    public function url(TpMedia $media): ?string;

    /**
     * @param array<string, scalar> $params
     */
    public function imageUrl(TpMedia $media, array $params = []): ?string;
}
