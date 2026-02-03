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
                    'date' => $parts[0] ?? '',
                    'title' => $parts[1] ?? '',
                    'body' => $parts[2] ?? '',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $items = $raw;
    } else {
        $items = [];
    }

    $items = array_values(array_filter($items, static fn ($item) => is_array($item) && (($item['title'] ?? '') !== '' || ($item['body'] ?? '') !== '')));
@endphp

@if ($items !== [])
    <section class="py-14 sm:py-20">
        <div class="mx-auto max-w-5xl space-y-8 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif

            <div class="space-y-8 border-l border-slate-200 pl-6">
                @foreach ($items as $item)
                    @php
                        $date = (string) ($item['date'] ?? '');
                        $itemTitle = (string) ($item['title'] ?? '');
                        $body = (string) ($item['body'] ?? '');
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-[18px] top-1 h-4 w-4 rounded-full border border-brand-200 bg-white shadow-sm"></div>
                        <div class="space-y-2">
                            @if ($date !== '')
                                <div class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">
                                    {{ $date }}
                                </div>
                            @endif
                            @if ($itemTitle !== '')
                                <div class="text-lg font-semibold text-slate-900">{{ $itemTitle }}</div>
                            @endif
                            @if ($body !== '')
                                <div class="text-sm text-slate-600">{{ $body }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
