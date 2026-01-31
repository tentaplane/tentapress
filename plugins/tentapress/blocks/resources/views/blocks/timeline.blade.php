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
    <section class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-6">
            @if ($title !== '')
                <h2 class="text-2xl font-semibold">{{ $title }}</h2>
            @endif

            <div class="space-y-6 border-l border-black/10 pl-6">
                @foreach ($items as $item)
                    @php
                        $date = (string) ($item['date'] ?? '');
                        $itemTitle = (string) ($item['title'] ?? '');
                        $body = (string) ($item['body'] ?? '');
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-[14px] top-1 h-3 w-3 rounded-full border border-black/20 bg-white"></div>
                        <div class="space-y-1">
                            @if ($date !== '')
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-black/50">
                                    {{ $date }}
                                </div>
                            @endif
                            @if ($itemTitle !== '')
                                <div class="text-lg font-semibold">{{ $itemTitle }}</div>
                            @endif
                            @if ($body !== '')
                                <div class="text-sm text-black/70">{{ $body }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
