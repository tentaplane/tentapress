@php
    $rawImages = $props['images'] ?? [];
    if (is_string($rawImages)) {
        $images = preg_split('/[\n,]+/', $rawImages) ?: [];
    } elseif (is_array($rawImages)) {
        $images = $rawImages;
    } else {
        $images = [];
    }

    $images = array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $images)));

    $columnsRaw = $props['columns'] ?? '3';
    $columns = (int) $columnsRaw;
    if ($columns < 2 || $columns > 5) {
        $columns = 3;
    }

    $gap = (string) ($props['gap'] ?? 'md');
    $gapClass = match ($gap) {
        'sm' => 'gap-2',
        'lg' => 'gap-6',
        default => 'gap-4',
    };

    $aspect = (string) ($props['aspect'] ?? '4:3');
    $aspectClass = match ($aspect) {
        'square' => 'aspect-square',
        '16:9' => 'aspect-video',
        '4:3' => 'aspect-[4/3]',
        default => '',
    };

    $rounded = filter_var($props['rounded'] ?? true, FILTER_VALIDATE_BOOL);

    $gridClass = match ($columns) {
        2 => 'grid-cols-2 md:grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-4',
        5 => 'grid-cols-2 md:grid-cols-5',
        default => 'grid-cols-2 md:grid-cols-3',
    };
@endphp

@if ($images !== [])
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid {{ $gapClass }} {{ $gridClass }}">
                @foreach ($images as $url)
                    @php
                        $path = parse_url($url, PHP_URL_PATH);
                        $filename = is_string($path) ? pathinfo($path, PATHINFO_FILENAME) : '';
                        $alt = $filename !== '' ? str_replace(['-', '_'], ' ', $filename) : '';
                        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
                        $imageRef = null;
                        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
                            $imageRef = $resolver->resolveImage(
                                ['url' => $url, 'alt' => $alt],
                                ['variant' => 'medium', 'sizes' => '(min-width: 1536px) 360px, (min-width: 1024px) 33vw, 50vw']
                            );
                        }
                        $imageSrc = is_array($imageRef) ? (string) ($imageRef['src'] ?? '') : $url;
                        $imageAlt = is_array($imageRef) ? (string) ($imageRef['alt'] ?? $alt) : $alt;
                        $imageSrcset = is_array($imageRef) ? ($imageRef['srcset'] ?? null) : null;
                        $imageSizes = is_array($imageRef) ? ($imageRef['sizes'] ?? null) : null;
                        $imageWidth = is_array($imageRef) && isset($imageRef['width']) && is_int($imageRef['width']) ? $imageRef['width'] : null;
                        $imageHeight = is_array($imageRef) && isset($imageRef['height']) && is_int($imageRef['height']) ? $imageRef['height'] : null;
                        $frameClass = 'overflow-hidden border border-black/[0.08] bg-white';
                        if ($rounded) {
                            $frameClass .= ' rounded-[2.5rem]';
                        }
                    @endphp
                    <figure class="{{ $frameClass }}">
                        <div class="overflow-hidden bg-surface-100 {{ $aspectClass }}">
                            <img
                                src="{{ $imageSrc }}"
                                alt="{{ $imageAlt }}"
                                @if (is_string($imageSrcset) && $imageSrcset !== '') srcset="{{ $imageSrcset }}" @endif
                                @if (is_string($imageSizes) && $imageSizes !== '') sizes="{{ $imageSizes }}" @endif
                                @if (is_int($imageWidth) && $imageWidth > 0) width="{{ $imageWidth }}" @endif
                                @if (is_int($imageHeight) && $imageHeight > 0) height="{{ $imageHeight }}" @endif
                                class="{{ $aspectClass !== '' ? 'h-full w-full object-cover' : 'h-auto w-full' }}"
                                loading="lazy"
                                decoding="async" />
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
