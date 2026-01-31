@php
    $title = (string) ($props['title'] ?? '');
    $subtitle = (string) ($props['subtitle'] ?? '');
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
                    'title' => $parts[0] ?? $line,
                    'body' => $parts[1] ?? '',
                    'icon' => $parts[2] ?? '',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $items = $raw;
    } else {
        $items = [];
    }

    $items = array_values(array_filter($items, static fn ($item) => is_array($item) && ($item['title'] ?? '') !== ''));

    $columns = (int) ($props['columns'] ?? 3);
    if ($columns < 2 || $columns > 4) {
        $columns = 3;
    }

    $gridClass = match ($columns) {
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-3',
    };
@endphp

@if ($items !== [])
    <section class="py-12">
        <div class="mx-auto max-w-6xl space-y-8 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-2">
                    @if ($title !== '')
                        <h2 class="text-2xl font-semibold">{{ $title }}</h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-black/60">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="grid gap-6 {{ $gridClass }}">
                @foreach ($items as $item)
                    @php
                        $itemTitle = (string) ($item['title'] ?? '');
                        $itemBody = (string) ($item['body'] ?? '');
                        $icon = (string) ($item['icon'] ?? '');
                    @endphp
                    <div class="rounded-xl border border-black/10 bg-white p-6">
                        @if ($icon !== '')
                            <div class="text-2xl">{{ $icon }}</div>
                        @endif
                        @if ($itemTitle !== '')
                            <div class="mt-3 text-lg font-semibold">{{ $itemTitle }}</div>
                        @endif
                        @if ($itemBody !== '')
                            <p class="mt-2 text-sm text-black/70">{{ $itemBody }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
