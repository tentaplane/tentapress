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
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid {{ $gapClass }} {{ $gridClass }}">
                @foreach ($images as $url)
                    @php
                        $path = parse_url($url, PHP_URL_PATH);
                        $filename = is_string($path) ? pathinfo($path, PATHINFO_FILENAME) : '';
                        $alt = $filename !== '' ? str_replace(['-', '_'], ' ', $filename) : '';
                        $frameClass = 'overflow-hidden border border-slate-200/80 bg-white shadow-sm';
                        if ($rounded) {
                            $frameClass .= ' rounded-2xl';
                        }
                    @endphp
                    <figure class="{{ $frameClass }}">
                        <div class="overflow-hidden bg-slate-100 {{ $aspectClass }}">
                            <img
                                src="{{ $url }}"
                                alt="{{ $alt }}"
                                class="{{ $aspectClass !== '' ? 'h-full w-full object-cover' : 'h-auto w-full' }}"
                                loading="lazy" />
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
