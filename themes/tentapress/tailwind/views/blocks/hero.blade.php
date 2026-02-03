@php
    $variant = isset($variant) ? (string) $variant : '';
    $eyebrow = (string) ($props['eyebrow'] ?? '');
    $headline = (string) ($props['headline'] ?? '');
    $sub = (string) ($props['subheadline'] ?? '');
    $bg = (string) ($props['background_image'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $imagePosition = (string) ($props['image_position'] ?? 'top');
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

    $ctaLabel = (string) ($primary['label'] ?? '');
    $ctaUrl = (string) ($primary['url'] ?? '');
    $ctaStyle = (string) ($primary['style'] ?? 'primary');
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';
    $splitLayout = $variant === 'split' || $imagePosition === 'right';
    $layoutClass = $splitLayout ? 'grid gap-14 lg:grid-cols-2 lg:items-center' : 'space-y-6';
    $hasBackground = $bg !== '' && ! $splitLayout;
    $titleClass = $hasBackground ? 'text-white' : 'text-slate-900';
    $subClass = $hasBackground ? 'text-white/80' : 'text-slate-500';
    $eyebrowClass = $hasBackground ? 'text-white/70' : 'text-brand-600';
    $contentWidthClass = $splitLayout ? '' : 'max-w-3xl';
    $contentAlignClass = $alignment === 'center' && ! $splitLayout ? 'mx-auto' : '';

    $ctaClass = match ($ctaStyle) {
        'outline' => $hasBackground ? 'border border-white/40 text-white' : 'border border-slate-200 text-slate-700',
        'ghost' => $hasBackground ? 'text-white/70 hover:text-white' : 'text-slate-500 hover:text-slate-900',
        default => 'bg-brand-600 text-white shadow-lg shadow-brand-600/30',
    };
@endphp

<section class="relative overflow-hidden py-24 sm:py-32">
    @if ($hasBackground)
        <div class="absolute inset-0">
            <img src="{{ $bg }}" alt="" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-slate-950/55 to-slate-900/30"></div>
        </div>
    @else
        <div class="pointer-events-none absolute -top-40 left-1/2 h-[30rem] w-[64rem] -translate-x-1/2 rounded-full bg-brand-300/40 blur-[160px]"></div>
        <div class="pointer-events-none absolute right-0 top-24 h-80 w-80 rounded-full bg-indigo-200/50 blur-[120px]"></div>
    @endif

    <div class="relative mx-auto max-w-7xl px-6">
        <div class="{{ $layoutClass }} {{ $alignClass }}">
            <div class="space-y-5 {{ $contentWidthClass }} {{ $contentAlignClass }}">
                @if ($eyebrow !== '')
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] {{ $eyebrowClass }}">
                        {{ $eyebrow }}
                    </div>
                @endif

                @if ($headline !== '')
                    <h1 class="text-balance font-display text-5xl font-semibold tracking-tight sm:text-6xl lg:text-7xl {{ $titleClass }}">
                        {{ $headline }}
                    </h1>
                @endif

                @if ($sub !== '')
                    <p class="text-pretty text-lg sm:text-xl {{ $subClass }}">{{ $sub }}</p>
                @endif

                @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                    <div class="mt-2 flex flex-wrap gap-3 {{ $actionsClass }}">
                        @if ($ctaLabel !== '' && $ctaUrl !== '')
                            <a
                                href="{{ $ctaUrl }}"
                                class="inline-flex items-center rounded-full px-6 py-3 text-sm font-semibold {{ $ctaClass }}">
                                {{ $ctaLabel }}
                            </a>
                        @endif

                        @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                            <a
                                href="{{ $secondaryUrl }}"
                                class="inline-flex items-center rounded-full border px-6 py-3 text-sm font-semibold {{ $hasBackground ? 'border-white/40 text-white/80 hover:text-white' : 'border-slate-200 text-slate-600 hover:text-slate-900' }}">
                                {{ $secondaryLabel }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if ($bg !== '' && $splitLayout)
                <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-100 shadow-lg shadow-slate-200/60">
                    <img src="{{ $bg }}" alt="" class="h-full w-full object-cover" />
                </div>
            @endif
        </div>
    </div>
</section>
