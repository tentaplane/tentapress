@php
    $title = (string) ($props['title'] ?? '');
    $subtitle = (string) ($props['subtitle'] ?? '');
    $rawLogos = $props['logos'] ?? [];

    if (is_string($rawLogos)) {
        $logos = preg_split('/[\n,]+/', $rawLogos) ?: [];
    } elseif (is_array($rawLogos)) {
        $logos = $rawLogos;
    } else {
        $logos = [];
    }

    $logos = array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $logos)));

    $columns = (int) ($props['columns'] ?? 5);
    if ($columns < 2 || $columns > 6) {
        $columns = 5;
    }

    $grayscale = filter_var($props['grayscale'] ?? true, FILTER_VALIDATE_BOOL);
    $size = (string) ($props['size'] ?? 'md');

    $gridClass = match ($columns) {
        2 => 'grid-cols-2 md:grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-4',
        5 => 'grid-cols-2 md:grid-cols-5',
        6 => 'grid-cols-2 md:grid-cols-6',
        default => 'grid-cols-2 md:grid-cols-5',
    };

    $logoClass = match ($size) {
        'sm' => 'h-8',
        'lg' => 'h-12',
        default => 'h-10',
    };
@endphp

@if ($logos !== [])
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-8 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-3 text-center">
                    @if ($title !== '')
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-surface-500">
                            {{ $title }}
                        </h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-pretty text-base text-surface-500">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="grid items-center gap-4 {{ $gridClass }}">
                @foreach ($logos as $logo)
                    @php
                        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
                        $logoRef = null;
                        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
                            $logoRef = $resolver->resolveImage(
                                ['url' => $logo, 'alt' => ''],
                                ['variant' => 'medium', 'sizes' => '(min-width: 1280px) 220px, (min-width: 768px) 18vw, 40vw']
                            );
                        }
                        $logoSrc = is_array($logoRef) ? (string) ($logoRef['src'] ?? '') : $logo;
                        $logoSrcset = is_array($logoRef) ? ($logoRef['srcset'] ?? null) : null;
                        $logoSizes = is_array($logoRef) ? ($logoRef['sizes'] ?? null) : null;
                        $logoWidth = is_array($logoRef) && isset($logoRef['width']) && is_int($logoRef['width']) ? $logoRef['width'] : null;
                        $logoHeight = is_array($logoRef) && isset($logoRef['height']) && is_int($logoRef['height']) ? $logoRef['height'] : null;
                    @endphp
                    <div class="flex items-center justify-center rounded-[2.5rem] border border-black/[0.08] bg-white px-6 py-5">
                        <img
                            src="{{ $logoSrc }}"
                            alt=""
                            @if (is_string($logoSrcset) && $logoSrcset !== '') srcset="{{ $logoSrcset }}" @endif
                            @if (is_string($logoSizes) && $logoSizes !== '') sizes="{{ $logoSizes }}" @endif
                            @if (is_int($logoWidth) && $logoWidth > 0) width="{{ $logoWidth }}" @endif
                            @if (is_int($logoHeight) && $logoHeight > 0) height="{{ $logoHeight }}" @endif
                            class="{{ $logoClass }} w-auto {{ $grayscale ? 'grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all' : '' }}"
                            loading="lazy"
                            decoding="async" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
