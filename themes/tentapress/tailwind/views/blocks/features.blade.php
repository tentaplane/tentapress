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
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-14 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="max-w-2xl space-y-4">
                    @if ($title !== '')
                        <h2 class="font-display text-4xl font-semibold tracking-tight text-surface-900 sm:text-5xl">
                            {{ $title }}
                        </h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-pretty text-lg leading-relaxed text-surface-600">{{ $subtitle }}</p>
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
                    <div class="rounded-[2.5rem] border border-black/[0.08] bg-white p-8">
                        @if ($icon !== '')
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-surface-100 text-xl text-surface-900">
                                {{ $icon }}
                            </div>
                        @endif
                        @if ($itemTitle !== '')
                            <div class="mt-5 font-display text-lg font-semibold text-surface-900">{{ $itemTitle }}</div>
                        @endif
                        @if ($itemBody !== '')
                            <p class="mt-2.5 text-[0.9375rem] leading-relaxed text-surface-600">{{ $itemBody }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
