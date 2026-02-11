@php
    $image = trim((string) ($props['image'] ?? ''));
    $alt = trim((string) ($props['alt'] ?? ''));
    $caption = trim((string) ($props['caption'] ?? ''));
    $link = is_array($props['link'] ?? null) ? $props['link'] : [];
    $linkUrl = trim((string) ($link['url'] ?? ''));
    $alignment = (string) ($props['alignment'] ?? 'center');
    $width = (string) ($props['width'] ?? 'normal');
    $rounded = filter_var($props['rounded'] ?? true, FILTER_VALIDATE_BOOL);
    $shadow = filter_var($props['shadow'] ?? false, FILTER_VALIDATE_BOOL);

    $widthClass = match ($width) {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-7xl',
        'full' => 'max-w-none',
        default => 'max-w-6xl',
    };

    $alignClass = match ($alignment) {
        'left' => 'mr-auto',
        'right' => 'ml-auto',
        default => 'mx-auto',
    };

    $figureClass = 'overflow-hidden border border-black/[0.08] bg-white';
    if ($rounded) {
        $figureClass .= ' rounded-[2.5rem]';
    }
    if ($shadow) {
        $figureClass .= ' shadow-lg';
    }
    $imageSizesConfig = match ($width) {
        'narrow' => '(min-width: 1280px) 768px, (min-width: 640px) calc(100vw - 3rem), calc(100vw - 2rem)',
        'wide' => '(min-width: 1536px) 1280px, (min-width: 640px) calc(100vw - 3rem), calc(100vw - 2rem)',
        'full' => '100vw',
        default => '(min-width: 1536px) 1152px, (min-width: 640px) calc(100vw - 3rem), calc(100vw - 2rem)',
    };

    $imageRef = null;
    if ($image !== '') {
        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $imageRef = $resolver->resolveImage(
                ['url' => $image, 'alt' => $alt],
                ['variant' => 'large', 'sizes' => $imageSizesConfig]
            );
        }
    }

    $imageSrc = is_array($imageRef) ? (string) ($imageRef['src'] ?? '') : $image;
    $imageAlt = is_array($imageRef) ? (string) ($imageRef['alt'] ?? $alt) : $alt;
    $imageSrcset = is_array($imageRef) ? ($imageRef['srcset'] ?? null) : null;
    $imageSizes = is_array($imageRef) ? ($imageRef['sizes'] ?? null) : null;
    $imageWidth = is_array($imageRef) && isset($imageRef['width']) && is_int($imageRef['width']) ? $imageRef['width'] : null;
    $imageHeight = is_array($imageRef) && isset($imageRef['height']) && is_int($imageRef['height']) ? $imageRef['height'] : null;
@endphp

@if ($imageSrc !== '')
<section class="py-16 sm:py-20">
        <div class="mx-auto px-6 {{ $widthClass }} {{ $alignClass }}">
            <figure class="{{ $figureClass }}">
                @if ($linkUrl !== '')
                    <a href="{{ $linkUrl }}" class="block">
                        <img
                            src="{{ $imageSrc }}"
                            alt="{{ $imageAlt }}"
                            @if (is_string($imageSrcset) && $imageSrcset !== '') srcset="{{ $imageSrcset }}" @endif
                            @if (is_string($imageSizes) && $imageSizes !== '') sizes="{{ $imageSizes }}" @endif
                            @if (is_int($imageWidth) && $imageWidth > 0) width="{{ $imageWidth }}" @endif
                            @if (is_int($imageHeight) && $imageHeight > 0) height="{{ $imageHeight }}" @endif
                            class="h-auto w-full"
                            loading="lazy"
                            decoding="async" />
                    </a>
                @else
                    <img
                        src="{{ $imageSrc }}"
                        alt="{{ $imageAlt }}"
                        @if (is_string($imageSrcset) && $imageSrcset !== '') srcset="{{ $imageSrcset }}" @endif
                        @if (is_string($imageSizes) && $imageSizes !== '') sizes="{{ $imageSizes }}" @endif
                        @if (is_int($imageWidth) && $imageWidth > 0) width="{{ $imageWidth }}" @endif
                        @if (is_int($imageHeight) && $imageHeight > 0) height="{{ $imageHeight }}" @endif
                        class="h-auto w-full"
                        loading="lazy"
                        decoding="async" />
                @endif

                @if ($caption !== '')
                    <figcaption class="border-t border-black/[0.08] px-6 py-4 text-sm text-surface-500">
                        {{ $caption }}
                    </figcaption>
                @endif
            </figure>
        </div>
    </section>
@endif
