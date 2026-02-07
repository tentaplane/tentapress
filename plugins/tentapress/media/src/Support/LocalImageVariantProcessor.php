<?php

declare(strict_types=1);

namespace TentaPress\Media\Support;

use Illuminate\Support\Facades\Storage;

final class LocalImageVariantProcessor
{
    private const MAX_ORIGINAL_WIDTH = 2048;

    private const MAX_ORIGINAL_HEIGHT = 2048;

    /**
     * @var array<string,array{max_width:int,quality:int}>
     */
    private const PRESETS = [
        'thumb' => ['max_width' => 320, 'quality' => 72],
        'medium' => ['max_width' => 768, 'quality' => 78],
        'large' => ['max_width' => 1600, 'quality' => 82],
    ];

    /**
     * @return array{
     *   size:int|null,
     *   width:int|null,
     *   height:int|null,
     *   source_width:int|null,
     *   source_height:int|null,
     *   variants:array<string,array{path:string,width:int,height:int,mime_type:string,size:int|null}>|null,
     *   preview_variant:string|null,
     *   optimization_status:string,
     *   optimization_error:string|null
     * }
     */
    public function process(string $disk, string $path, ?string $mimeType): array
    {
        $mime = strtolower(trim((string) ($mimeType ?? '')));

        if ($path === '' || ! str_starts_with($mime, 'image/')) {
            return [
                'size' => $this->fileSize($disk, $path),
                'width' => null,
                'height' => null,
                'source_width' => null,
                'source_height' => null,
                'variants' => null,
                'preview_variant' => null,
                'optimization_status' => 'skipped',
                'optimization_error' => null,
            ];
        }

        $storage = Storage::disk($disk);
        $fullPath = $storage->path($path);

        if ($fullPath === '' || ! is_file($fullPath)) {
            return [
                'size' => null,
                'width' => null,
                'height' => null,
                'source_width' => null,
                'source_height' => null,
                'variants' => null,
                'preview_variant' => null,
                'optimization_status' => 'failed',
                'optimization_error' => 'Media file does not exist on disk.',
            ];
        }

        $sourceDimensions = $this->dimensions($fullPath);
        $sourceWidth = $sourceDimensions['width'];
        $sourceHeight = $sourceDimensions['height'];

        if ($sourceWidth === null || $sourceHeight === null) {
            return [
                'size' => $this->fileSize($disk, $path),
                'width' => null,
                'height' => null,
                'source_width' => null,
                'source_height' => null,
                'variants' => null,
                'preview_variant' => null,
                'optimization_status' => 'skipped',
                'optimization_error' => null,
            ];
        }

        $status = 'ready';
        $error = null;

        if ($sourceWidth > self::MAX_ORIGINAL_WIDTH || $sourceHeight > self::MAX_ORIGINAL_HEIGHT) {
            $resized = $this->resizeImageToPath(
                sourcePath: $fullPath,
                targetPath: $fullPath,
                mimeType: $mime,
                maxWidth: self::MAX_ORIGINAL_WIDTH,
                maxHeight: self::MAX_ORIGINAL_HEIGHT,
                quality: self::PRESETS['large']['quality'],
            );

            if (! $resized) {
                $status = 'failed';
                $error = 'Unable to resize uploaded image to ingest clamp.';
            }
        }

        $currentDimensions = $this->dimensions($fullPath);
        $currentWidth = $currentDimensions['width'];
        $currentHeight = $currentDimensions['height'];

        $variants = null;
        $previewVariant = null;

        if ($status === 'ready' && $currentWidth !== null && $currentHeight !== null) {
            $variantResult = $this->buildVariants(
                disk: $disk,
                path: $path,
                fullPath: $fullPath,
                mimeType: $mime,
                width: $currentWidth,
                height: $currentHeight,
            );

            $variants = $variantResult['variants'];
            $previewVariant = $variantResult['preview_variant'];
        }

        return [
            'size' => $this->fileSize($disk, $path),
            'width' => $currentWidth,
            'height' => $currentHeight,
            'source_width' => $sourceWidth,
            'source_height' => $sourceHeight,
            'variants' => $variants,
            'preview_variant' => $previewVariant,
            'optimization_status' => $status,
            'optimization_error' => $error,
        ];
    }

    /**
     * @return array{width:int|null,height:int|null}
     */
    private function dimensions(string $fullPath): array
    {
        $size = @getimagesize($fullPath);

        if (! is_array($size)) {
            return [
                'width' => null,
                'height' => null,
            ];
        }

        return [
            'width' => isset($size[0]) ? (int) $size[0] : null,
            'height' => isset($size[1]) ? (int) $size[1] : null,
        ];
    }

    /**
     * @return array{variants:array<string,array{path:string,width:int,height:int,mime_type:string,size:int|null}>|null,preview_variant:string|null}
     */
    private function buildVariants(
        string $disk,
        string $path,
        string $fullPath,
        string $mimeType,
        int $width,
        int $height,
    ): array {
        $variants = [];
        $previewVariant = null;

        $directory = trim((string) pathinfo($path, PATHINFO_DIRNAME), '.');
        $basename = (string) pathinfo($path, PATHINFO_FILENAME);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $variantDirectory = ($directory !== '' ? $directory.'/' : '').'variants';

        Storage::disk($disk)->makeDirectory($variantDirectory);

        foreach (self::PRESETS as $key => $preset) {
            $targetWidth = (int) $preset['max_width'];

            if ($width <= $targetWidth) {
                continue;
            }

            $targetHeight = max(1, (int) floor(($height / $width) * $targetWidth));
            $variantPath = $variantDirectory.'/'.$basename.'-'.$key.($extension !== '' ? '.'.$extension : '');
            $variantFullPath = Storage::disk($disk)->path($variantPath);

            $written = $this->resizeImageToPath(
                sourcePath: $fullPath,
                targetPath: $variantFullPath,
                mimeType: $mimeType,
                maxWidth: $targetWidth,
                maxHeight: $targetHeight,
                quality: (int) $preset['quality'],
            );

            if (! $written) {
                continue;
            }

            $variantSize = $this->dimensions($variantFullPath);

            if ($variantSize['width'] === null || $variantSize['height'] === null) {
                continue;
            }

            $variants[$key] = [
                'path' => $variantPath,
                'width' => $variantSize['width'],
                'height' => $variantSize['height'],
                'mime_type' => $mimeType,
                'size' => $this->fileSize($disk, $variantPath),
            ];

            if ($previewVariant === null) {
                $previewVariant = $key;
            }
        }

        return [
            'variants' => $variants !== [] ? $variants : null,
            'preview_variant' => $previewVariant,
        ];
    }

    private function resizeImageToPath(
        string $sourcePath,
        string $targetPath,
        string $mimeType,
        int $maxWidth,
        int $maxHeight,
        int $quality,
    ): bool {
        $source = $this->createImageResource($sourcePath, $mimeType);

        if ($source === null) {
            return false;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);

            return false;
        }

        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);

        $targetWidth = max(1, (int) floor($sourceWidth * $ratio));
        $targetHeight = max(1, (int) floor($sourceHeight * $ratio));

        if ($targetWidth === $sourceWidth && $targetHeight === $sourceHeight && $sourcePath === $targetPath) {
            imagedestroy($source);

            return true;
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            imagedestroy($source);

            return false;
        }

        if (in_array($mimeType, ['image/png', 'image/webp', 'image/gif'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        $resampled = imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        if (! $resampled) {
            imagedestroy($source);
            imagedestroy($target);

            return false;
        }

        $written = $this->writeImageResource($target, $targetPath, $mimeType, $quality);

        imagedestroy($source);
        imagedestroy($target);

        return $written;
    }

    private function createImageResource(string $path, string $mimeType): mixed
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default => null,
        };
    }

    private function writeImageResource(mixed $image, string $path, string $mimeType, int $quality): bool
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagejpeg($image, $path, max(1, min(100, $quality))),
            'image/png' => imagepng($image, $path, $this->pngCompressionFromQuality($quality)),
            'image/gif' => imagegif($image, $path),
            'image/webp' => function_exists('imagewebp')
                ? imagewebp($image, $path, max(1, min(100, $quality)))
                : false,
            default => false,
        };
    }

    private function pngCompressionFromQuality(int $quality): int
    {
        $normalizedQuality = max(1, min(100, $quality));

        return (int) round((100 - $normalizedQuality) * 9 / 100);
    }

    private function fileSize(string $disk, string $path): ?int
    {
        if ($path === '') {
            return null;
        }

        try {
            return Storage::disk($disk)->size($path);
        } catch (\Throwable) {
            return null;
        }
    }
}
