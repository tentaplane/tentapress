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
    <section class="py-20 sm:py-24">
        <div class="mx-auto max-w-7xl space-y-12 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-3">
                    @if ($title !== '')
                        <h2 class="font-display text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                            {{ $title }}
                        </h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-pretty text-lg text-slate-500">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="grid gap-8 {{ $gridClass }}">
                @foreach ($items as $item)
                    @php
                        $itemTitle = (string) ($item['title'] ?? '');
                        $itemBody = (string) ($item['body'] ?? '');
                        $icon = (string) ($item['icon'] ?? '');
                    @endphp
                    <div class="group relative rounded-3xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-200/60">
                        <div class="pointer-events-none absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent via-brand-200 to-transparent"></div>
                        @if ($icon !== '')
                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-2xl text-brand-600">
                                {{ $icon }}
                            </div>
                        @endif
                        @if ($itemTitle !== '')
                            <div class="mt-5 text-xl font-semibold text-slate-900">{{ $itemTitle }}</div>
                        @endif
                        @if ($itemBody !== '')
                            <p class="mt-3 text-base text-slate-600">{{ $itemBody }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
