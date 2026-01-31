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
                if ($line === '') continue;
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
    <section class="py-12">
        <div class="mx-auto max-w-6xl space-y-6 px-6">
            @if ($title !== '')
                <h2 class="text-2xl font-semibold">{{ $title }}</h2>
            @endif

            <div class="grid gap-6 {{ $gridClass }}">
                @foreach ($items as $item)
                    @php
                        $value = (string) ($item['value'] ?? '');
                        $label = (string) ($item['label'] ?? '');
                        $cardClass = 'rounded-xl border border-black/10 bg-white p-6';
                        if ($dividers && $loop->index > 0) {
                            $cardClass .= ' border-l-4 border-l-black/10';
                        }
                    @endphp
                    <div class="{{ $cardClass }}">
                        <div class="text-3xl font-semibold">{{ $value }}</div>
                        @if ($label !== '')
                            <div class="mt-2 text-sm text-black/60">{{ $label }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
