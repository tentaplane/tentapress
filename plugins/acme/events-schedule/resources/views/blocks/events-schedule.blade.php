@php
    $title = (string) ($props['title'] ?? '');
    $subtitle = (string) ($props['subtitle'] ?? '');
    $raw = $props['events'] ?? [];

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
                    'time' => $parts[1] ?? '',
                    'title' => $parts[2] ?? '',
                    'location' => $parts[3] ?? '',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $items = $raw;
    } else {
        $items = [];
    }

    $items = array_values(array_filter(
        $items,
        static fn ($item): bool => is_array($item) && (($item['title'] ?? '') !== '' || ($item['date'] ?? '') !== '')
    ));
@endphp

@if ($items !== [])
    <section class="py-12">
        <div class="mx-auto max-w-5xl space-y-8 px-6">
            @if ($title !== '' || $subtitle !== '')
                <div class="space-y-2">
                    @if ($title !== '')
                        <h2 class="text-3xl font-semibold">{{ $title }}</h2>
                    @endif
                    @if ($subtitle !== '')
                        <p class="text-sm text-black/70">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-black/10 bg-white/80">
                <div class="divide-y divide-black/10">
                    @foreach ($items as $item)
                        @php
                            $date = (string) ($item['date'] ?? '');
                            $time = (string) ($item['time'] ?? '');
                            $eventTitle = (string) ($item['title'] ?? '');
                            $location = (string) ($item['location'] ?? '');
                            $meta = trim($date.' '.$time);
                        @endphp
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-1">
                                @if ($eventTitle !== '')
                                    <div class="text-lg font-semibold">{{ $eventTitle }}</div>
                                @endif
                                @if ($location !== '')
                                    <div class="text-sm text-black/60">{{ $location }}</div>
                                @endif
                            </div>
                            @if ($meta !== '')
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-black/50">
                                    {{ $meta }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
