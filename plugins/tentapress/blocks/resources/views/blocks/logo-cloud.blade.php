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
        'lg' => 'h-14',
        default => 'h-10',
    };
@endphp

@if ($logos !== [])
    <section class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-2 text-center">
                    @if ($title !== '')
                        <h2 class="text-2xl font-semibold">{{ $title }}</h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-sm text-black/60">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="grid items-center gap-6 {{ $gridClass }}">
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
                    @endphp
                    <div class="flex items-center justify-center rounded-lg border border-black/10 bg-white p-4">
                        <img
                            src="{{ $logoSrc }}"
                            alt=""
                            @if (is_string($logoSrcset) && $logoSrcset !== '') srcset="{{ $logoSrcset }}" @endif
                            @if (is_string($logoSizes) && $logoSizes !== '') sizes="{{ $logoSizes }}" @endif
                            class="{{ $logoClass }} w-auto {{ $grayscale ? 'grayscale opacity-70' : '' }}"
                            loading="lazy"
                            decoding="async" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
