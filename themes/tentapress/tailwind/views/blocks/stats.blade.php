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
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-12 px-6">
            @if ($title !== '')
                <h2 class="font-display text-4xl font-semibold text-surface-900 sm:text-5xl">
                    {{ $title }}
                </h2>
            @endif

            <div class="grid gap-6 {{ $gridClass }}">
                @foreach ($items as $item)
                    @php
                        $value = (string) ($item['value'] ?? '');
                        $label = (string) ($item['label'] ?? '');
                        $cardClass = 'rounded-[2.5rem] border border-black/[0.08] bg-white p-8';
                        if ($dividers && $loop->index > 0) {
                            $cardClass .= ' md:border-l-2 md:border-l-surface-200 md:rounded-l-none';
                        }
                    @endphp
                    <div class="{{ $cardClass }}">
                        <div class="font-display text-5xl font-semibold text-surface-900">{{ $value }}</div>
                        @if ($label !== '')
                            <div class="mt-3 text-sm font-medium text-surface-500">{{ $label }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
