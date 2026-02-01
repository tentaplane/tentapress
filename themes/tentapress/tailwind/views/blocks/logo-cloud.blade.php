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
        <div class="mx-auto max-w-6xl space-y-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-2 text-center">
                    @if ($title !== '')
                        <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">{{ $title }}</h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-sm text-slate-500">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="grid items-center gap-4 {{ $gridClass }}">
                @foreach ($logos as $logo)
                    @php
                        $isUrl = filter_var($logo, FILTER_VALIDATE_URL) !== false || str_starts_with($logo, 'data:');
                    @endphp
                    <div class="flex items-center justify-center rounded-2xl border border-slate-200/70 bg-white px-4 py-3">
                        @if ($isUrl)
                            <img
                                src="{{ $logo }}"
                                alt=""
                                class="{{ $logoClass }} w-auto {{ $grayscale ? 'grayscale opacity-70' : '' }}" />
                        @else
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $logo }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
