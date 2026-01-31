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
                $parts = array_map('trim', explode('|', $line, 2));
                $items[] = [
                    'question' => $parts[0] ?? $line,
                    'answer' => $parts[1] ?? '',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $items = $raw;
    } else {
        $items = [];
    }

    $items = array_values(array_filter($items, static fn ($item) => is_array($item) && ($item['question'] ?? '') !== ''));

    $openFirst = filter_var($props['open_first'] ?? false, FILTER_VALIDATE_BOOL);
@endphp

@if ($items !== [])
    <section class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-6">
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

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    @php
                        $question = (string) ($item['question'] ?? '');
                        $answer = (string) ($item['answer'] ?? '');
                    @endphp
                    <details class="rounded-xl border border-black/10 bg-white p-5" @if ($openFirst && $index === 0) open @endif>
                        <summary class="cursor-pointer text-sm font-semibold">
                            {{ $question }}
                        </summary>
                        @if ($answer !== '')
                            <div class="mt-3 text-sm text-black/70">
                                {{ $answer }}
                            </div>
                        @endif
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
