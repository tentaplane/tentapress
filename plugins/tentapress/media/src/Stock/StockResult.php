<?php

declare(strict_types=1);

namespace TentaPress\Media\Stock;

final readonly class StockResult
{
    /**
     * @param array<string,mixed> $meta
     */
    public function __construct(
        public string $id,
        public string $provider,
        public string $title,
        public string $author,
        public ?string $authorUrl,
        public ?string $sourceUrl,
        public ?string $license,
        public ?string $licenseUrl,
        public ?string $previewUrl,
        public ?string $downloadUrl,
        public ?int $width,
        public ?int $height,
        public ?string $mediaType,
        public ?string $attribution,
        public ?string $attributionHtml,
        public array $meta = [],
    ) {
    }
}
