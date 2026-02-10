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
        'wide' => 'max-w-6xl',
        'full' => 'max-w-none',
        default => 'max-w-5xl',
    };

    $alignClass = match ($alignment) {
        'left' => 'mr-auto',
        'right' => 'ml-auto',
        default => 'mx-auto',
    };

    $figureClass = 'overflow-hidden border border-black/10 bg-white';
    if ($rounded) {
        $figureClass .= ' rounded-xl';
    }
    if ($shadow) {
        $figureClass .= ' shadow-sm';
    }

    $imageRef = null;
    if ($image !== '') {
        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $imageRef = $resolver->resolveImage(
                ['url' => $image, 'alt' => $alt],
                ['variant' => 'large', 'sizes' => '(min-width: 1024px) 960px, 100vw']
            );
        }
    }

    $imageSrc = is_array($imageRef) ? (string) ($imageRef['src'] ?? '') : $image;
    $imageAlt = is_array($imageRef) ? (string) ($imageRef['alt'] ?? $alt) : $alt;
    $imageSrcset = is_array($imageRef) ? ($imageRef['srcset'] ?? null) : null;
    $imageSizes = is_array($imageRef) ? ($imageRef['sizes'] ?? null) : null;
@endphp

@if ($imageSrc !== '')
    <section class="py-10">
        <div class="mx-auto px-6 {{ $widthClass }} {{ $alignClass }}">
            <figure class="{{ $figureClass }}">
                @if ($linkUrl !== '')
                    <a href="{{ $linkUrl }}" class="block">
                        <img
                            src="{{ $imageSrc }}"
                            alt="{{ $imageAlt }}"
                            @if (is_string($imageSrcset) && $imageSrcset !== '') srcset="{{ $imageSrcset }}" @endif
                            @if (is_string($imageSizes) && $imageSizes !== '') sizes="{{ $imageSizes }}" @endif
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
                        class="h-auto w-full"
                        loading="lazy"
                        decoding="async" />
                @endif

                @if ($caption !== '')
                    <figcaption class="border-t border-black/10 px-4 py-3 text-sm text-black/70">
                        {{ $caption }}
                    </figcaption>
                @endif
            </figure>
        </div>
    </section>
@endif
