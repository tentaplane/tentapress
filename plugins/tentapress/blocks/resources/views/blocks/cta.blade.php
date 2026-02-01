@php
    $title = (string) ($props['title'] ?? '');
    $body = (string) ($props['body'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');
    $rawActions = $props['actions'] ?? [];
    if (is_string($rawActions)) {
        $trim = trim($rawActions);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        if (is_array($decoded)) {
            $actions = $decoded;
        } else {
            $lines = preg_split('/\r?\n/', $trim) ?: [];
            $actions = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $actions[] = [
                    'label' => $parts[0] ?? $line,
                    'url' => $parts[1] ?? '',
                    'style' => $parts[2] ?? 'primary',
                ];
            }
        }
    } elseif (is_array($rawActions)) {
        $actions = $rawActions;
    } else {
        $actions = [];
    }

    $actions = array_values(array_filter($actions, static fn ($item) => is_array($item) && ($item['label'] ?? '') !== ''));

    $primary = $actions[0] ?? [];
    $secondary = $actions[1] ?? [];

    $btnLabel = (string) ($primary['label'] ?? '');
    $btnUrl = (string) ($primary['url'] ?? '');
    $btnStyle = (string) ($primary['style'] ?? 'primary');
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';

    $panelClass = match ($background) {
        'none' => 'bg-transparent border border-black/10',
        'muted' => 'bg-slate-50 border border-black/5',
        default => 'bg-white border border-black/10',
    };

    $btnClass = match ($btnStyle) {
        'outline' => 'border border-black/20 text-black',
        'ghost' => 'text-black/70 hover:text-black',
        default => 'bg-black text-white',
    };
@endphp

<section class="py-10">
    <div class="mx-auto max-w-5xl px-6">
        <div class="rounded-xl p-8 {{ $panelClass }} {{ $alignClass }}">
            @if ($title !== '')
                <h2 class="text-2xl font-semibold">{{ $title }}</h2>
            @endif

            @if ($body !== '')
                <p class="mt-3 whitespace-pre-wrap text-black/70">{{ $body }}</p>
            @endif

            @if (($btnLabel !== '' && $btnUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                <div class="mt-6 flex flex-wrap gap-3 {{ $actionsClass }}">
                    @if ($btnLabel !== '' && $btnUrl !== '')
                        <a
                            href="{{ $btnUrl }}"
                            class="inline-flex items-center rounded px-4 py-2 text-sm font-semibold {{ $btnClass }}">
                            {{ $btnLabel }}
                        </a>
                    @endif

                    @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                        <a
                            href="{{ $secondaryUrl }}"
                            class="inline-flex items-center rounded border border-black/10 px-4 py-2 text-sm font-semibold text-black/70 hover:text-black">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
