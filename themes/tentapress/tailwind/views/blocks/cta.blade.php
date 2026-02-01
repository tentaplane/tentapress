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

    $buttonLabel = (string) ($primary['label'] ?? '');
    $buttonUrl = (string) ($primary['url'] ?? '');
    $buttonStyle = (string) ($primary['style'] ?? 'primary');
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'items-center text-center' : 'items-start text-left';

    $panelClass = match ($background) {
        'none' => 'bg-transparent',
        'muted' => 'bg-slate-900 text-white',
        default => 'bg-white',
    };

    $ctaClass = match ($buttonStyle) {
        'outline' => 'border border-slate-200 text-slate-900 hover:border-slate-300',
        'ghost' => $background === 'muted' ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-slate-900',
        default => $background === 'muted' ? 'bg-white text-slate-900 hover:bg-slate-100' : 'bg-slate-900 text-white hover:bg-slate-800',
    };
@endphp

@if ($title !== '' || $body !== '')
    <section class="py-12">
        <div class="mx-auto max-w-5xl">
            <div
                class="{{ $panelClass }} {{ $alignClass }} flex flex-col gap-6 rounded-3xl border border-slate-200/70 px-8 py-10 shadow-sm">
                @if ($title !== '')
                    <h2 class="text-3xl font-semibold md:text-4xl">{{ $title }}</h2>
                @endif

                @if ($body !== '')
                    <p class="{{ $background === 'muted' ? 'text-white/80' : 'text-slate-600' }} text-base md:text-lg">
                        {{ $body }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-4">
                    @if ($buttonLabel !== '' && $buttonUrl !== '')
                        <a
                            href="{{ $buttonUrl }}"
                            class="{{ $ctaClass }} inline-flex items-center rounded-full px-6 py-3 text-sm font-semibold shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5">
                            {{ $buttonLabel }}
                        </a>
                    @endif

                    @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                        <a
                            href="{{ $secondaryUrl }}"
                            class="{{ $background === 'muted' ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-slate-900' }} inline-flex items-center rounded-full border border-white/20 px-5 py-3 text-sm font-semibold">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
