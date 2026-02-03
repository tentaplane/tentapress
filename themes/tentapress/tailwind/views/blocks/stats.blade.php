@php
    $title = (string) ($props['title'] ?? '');
    $raw = $props['items'] ?? [];

    if (is_string($raw)) {
        $trim = trim($raw);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        if (is_array($decoded)) {
            $items = $decoded;
        } else {
            $lines = preg_split('/\r?\n/', $trim) ?: [];
            $items = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $items[] = [
                    'value' => $parts[0] ?? $line,
                    'label' => $parts[1] ?? '',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $items = $raw;
    } else {
        $items = [];
    }

    $items = array_values(array_filter($items, static fn ($item) => is_array($item) && ($item['value'] ?? '') !== ''));

    $columns = (int) ($props['columns'] ?? 3);
    if ($columns < 2 || $columns > 4) {
        $columns = 3;
    }

    $dividers = filter_var($props['dividers'] ?? false, FILTER_VALIDATE_BOOL);

    $gridClass = match ($columns) {
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-3',
    };
@endphp

@if ($items !== [])
    <section class="py-20 sm:py-24">
        <div class="mx-auto max-w-7xl space-y-10 px-6">
            @if ($title !== '')
                <h2 class="font-display text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                    {{ $title }}
                </h2>
            @endif

            <div class="grid gap-8 {{ $gridClass }}">
                @foreach ($items as $item)
                    @php
                        $value = (string) ($item['value'] ?? '');
                        $label = (string) ($item['label'] ?? '');
                        $cardClass = 'rounded-3xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-200/60';
                        if ($dividers && $loop->index > 0) {
                            $cardClass .= ' lg:border-l-4 lg:border-l-brand-100 lg:pl-8';
                        }
                    @endphp
                    <div class="{{ $cardClass }}">
                        <div class="font-display text-4xl font-semibold text-slate-900">{{ $value }}</div>
                        @if ($label !== '')
                            <div class="mt-2 text-base text-slate-500">{{ $label }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
