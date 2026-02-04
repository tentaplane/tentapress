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
@endphp

@if ($image !== '')
<section class="py-16 sm:py-20">
        <div class="mx-auto px-6 {{ $widthClass }} {{ $alignClass }}">
            <figure class="{{ $figureClass }}">
                @if ($linkUrl !== '')
                    <a href="{{ $linkUrl }}" class="block">
                        <img src="{{ $image }}" alt="{{ $alt }}" class="h-auto w-full" loading="lazy" />
                    </a>
                @else
                    <img src="{{ $image }}" alt="{{ $alt }}" class="h-auto w-full" loading="lazy" />
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
