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

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';

    $panelClass = match ($background) {
        'none' => 'bg-transparent border-transparent',
        'muted' => 'bg-slate-100/80 border-slate-200/70',
        default => 'bg-white border-slate-200/80 shadow-sm',
    };

    $btnClass = match ($btnStyle) {
        'outline' => 'border border-slate-200 text-slate-700',
        'ghost' => 'text-slate-500 hover:text-slate-900',
        default => 'bg-brand-600 text-white shadow-lg shadow-brand-600/30',
    };
@endphp

<section class="py-14 sm:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="relative overflow-hidden rounded-3xl border {{ $panelClass }} p-8 sm:p-12">
            <div class="pointer-events-none absolute -left-12 top-0 h-32 w-32 rounded-full bg-brand-100/70 blur-[90px]"></div>
            <div class="pointer-events-none absolute -right-16 top-8 h-40 w-40 rounded-full bg-indigo-200/40 blur-[110px]"></div>

            <div class="relative flex flex-col gap-4 {{ $alignClass }}">
                @if ($title !== '')
                    <h2 class="text-balance font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                        {{ $title }}
                    </h2>
                @endif

                @if ($body !== '')
                    <p class="text-pretty whitespace-pre-wrap text-base text-slate-500">{{ $body }}</p>
                @endif

                @if (($btnLabel !== '' && $btnUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                    <div class="mt-2 flex flex-wrap gap-3 {{ $actionsClass }}">
                        @if ($btnLabel !== '' && $btnUrl !== '')
                            <a
                                href="{{ $btnUrl }}"
                                class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold {{ $btnClass }}">
                                {{ $btnLabel }}
                            </a>
                        @endif

                        @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                            <a
                                href="{{ $secondaryUrl }}"
                                class="inline-flex items-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                                {{ $secondaryLabel }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
