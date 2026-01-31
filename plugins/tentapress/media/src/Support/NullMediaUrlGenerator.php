<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Models\TpMedia;

final class NullMediaUrlGenerator implements MediaUrlGenerator
{
    public function url(TpMedia $media): ?string
    {
        return null;
    }

    public function imageUrl(TpMedia $media, array $params = []): ?string
    {
        return $this->url($media);
    }
}
