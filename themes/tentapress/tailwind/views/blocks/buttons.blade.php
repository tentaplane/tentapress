@php
    $raw = $props['actions'] ?? [];

    if (is_string($raw)) {
        $trim = trim($raw);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        if (is_array($decoded)) {
            $buttons = $decoded;
        } else {
            $lines = preg_split('/\r?\n/', $trim) ?: [];
            $buttons = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $buttons[] = [
                    'label' => $parts[0] ?? $line,
                    'url' => $parts[1] ?? '#',
                    'style' => $parts[2] ?? 'primary',
                ];
            }
        }
    } elseif (is_array($raw)) {
        $buttons = $raw;
    } else {
        $buttons = [];
    }

    $buttons = array_values(array_filter($buttons, static fn ($item) => is_array($item) && ($item['label'] ?? '') !== ''));

    $alignment = (string) ($props['alignment'] ?? 'left');
    $size = (string) ($props['size'] ?? 'md');

    $alignClass = match ($alignment) {
        'center' => 'justify-center',
        'right' => 'justify-end',
        default => 'justify-start',
    };

    $sizeClass = match ($size) {
        'sm' => 'px-4 py-2 text-xs',
        'lg' => 'px-6 py-3 text-sm',
        default => 'px-5 py-2.5 text-sm',
    };
@endphp

@if ($buttons !== [])
    <section class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-wrap gap-3 {{ $alignClass }}">
                @foreach ($buttons as $button)
                    @php
                        $label = (string) ($button['label'] ?? '');
                        $url = (string) ($button['url'] ?? '#');
                        $style = (string) ($button['style'] ?? 'primary');

                        $class = match ($style) {
                            'outline' => 'border border-black/[0.08] text-surface-700 hover:bg-surface-50',
                            'ghost' => 'text-surface-600 hover:text-surface-900',
                            default => 'bg-surface-900 text-white hover:opacity-80',
                        };
                    @endphp
                    <a href="{{ $url }}" class="inline-flex items-center rounded-lg font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2 {{ $sizeClass }} {{ $class }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
