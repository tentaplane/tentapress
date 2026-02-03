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
    <section class="py-20 sm:py-24">
        <div class="mx-auto max-w-6xl space-y-10 px-6">
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

            <div class="space-y-5">
                @foreach ($items as $index => $item)
                    @php
                        $question = (string) ($item['question'] ?? '');
                        $answer = (string) ($item['answer'] ?? '');
                    @endphp
                    <details class="rounded-3xl border border-slate-200/80 bg-white p-7 shadow-lg shadow-slate-200/60" @if ($openFirst && $index === 0) open @endif>
                        <summary class="cursor-pointer text-base font-semibold text-slate-900">
                            {{ $question }}
                        </summary>
                        @if ($answer !== '')
                            <div class="mt-3 text-sm text-slate-600">
                                {{ $answer }}
                            </div>
                        @endif
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
