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
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-4xl space-y-12 px-6">
            @if ($title !== '')
                <h2 class="font-display text-4xl font-semibold text-surface-900 sm:text-5xl">
                    {{ $title }}
                </h2>
            @endif

            <div class="space-y-12 border-l-2 border-black/[0.08] pl-8">
                @foreach ($items as $item)
                    @php
                        $date = (string) ($item['date'] ?? '');
                        $itemTitle = (string) ($item['title'] ?? '');
                        $body = (string) ($item['body'] ?? '');
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1.5 h-3 w-3 rounded-full border-2 border-surface-900 bg-white"></div>
                        <div class="space-y-2">
                            @if ($date !== '')
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-surface-500">
                                    {{ $date }}
                                </div>
                            @endif
                            @if ($itemTitle !== '')
                                <div class="font-display text-xl font-semibold text-surface-900">{{ $itemTitle }}</div>
                            @endif
                            @if ($body !== '')
                                <div class="text-[0.9375rem] leading-relaxed text-surface-600">{{ $body }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
