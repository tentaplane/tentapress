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
                if ($line === '') {
                    continue;
                }
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
    <section class="py-12" id="features">
        <div class="mx-auto max-w-6xl space-y-10">
            @if ($title !== '' || $subtitle !== '')
                <div class="max-w-2xl space-y-3">
                    @if ($title !== '')
                        <h2 class="text-3xl font-semibold text-slate-900 md:text-4xl">{{ $title }}</h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-base text-slate-600 md:text-lg">{{ $subtitle }}</p>
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
                    <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white">
                            {{ $icon !== '' ? $icon : '⬢' }}
                        </div>
                        @if ($itemTitle !== '')
                            <div class="mt-5 text-lg font-semibold text-slate-900">{{ $itemTitle }}</div>
                        @endif
                        @if ($itemBody !== '')
                            <p class="mt-2 text-sm text-slate-600">{{ $itemBody }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
