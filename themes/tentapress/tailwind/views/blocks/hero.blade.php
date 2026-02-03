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
    $titleClass = $hasBackground ? 'text-white' : 'text-surface-900';
    $subClass = $hasBackground ? 'text-white/85' : 'text-surface-600';
    $eyebrowClass = $hasBackground ? 'text-white/70' : 'text-brand-600';
    $contentWidthClass = $splitLayout ? '' : 'max-w-3xl';
    $contentAlignClass = $alignment === 'center' && ! $splitLayout ? 'mx-auto' : '';

    $ctaClass = match ($ctaStyle) {
        'outline' => $hasBackground ? 'border border-white/40 text-white hover:bg-white/10' : 'border border-surface-300 text-surface-700 hover:border-surface-400 hover:bg-surface-50',
        'ghost' => $hasBackground ? 'text-white/70 hover:text-white' : 'text-surface-600 hover:text-surface-900',
        default => 'bg-brand-600 text-white shadow-brand hover:bg-brand-700 hover:shadow-xl',
    };
@endphp

<section class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen overflow-hidden py-24 sm:py-32">
    @if ($hasBackground)
        <div class="absolute inset-0">
            <img src="{{ $bg }}" alt="" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-br from-surface-950/85 via-surface-950/60 to-surface-900/40"></div>
        </div>
    @else
        <div class="pointer-events-none absolute -top-48 left-1/2 h-[32rem] w-[72rem] -translate-x-1/2 rounded-full bg-brand-200/50 blur-[180px]"></div>
        <div class="pointer-events-none absolute right-0 top-32 h-72 w-72 rounded-full bg-accent-200/40 blur-[120px]"></div>
    @endif

    <div class="relative mx-auto max-w-7xl px-6">
        <div class="{{ $layoutClass }} {{ $alignClass }}">
            <div class="space-y-6 {{ $contentWidthClass }} {{ $contentAlignClass }}">
                @if ($eyebrow !== '')
                    <div class="text-xs font-semibold uppercase tracking-[0.25em] {{ $eyebrowClass }}">
                        {{ $eyebrow }}
                    </div>
                @endif

                @if ($headline !== '')
                    <h1 class="text-balance font-display text-5xl font-semibold tracking-tight sm:text-6xl lg:text-7xl {{ $titleClass }}">
                        {{ $headline }}
                    </h1>
                @endif

                @if ($sub !== '')
                    <p class="text-pretty text-lg leading-relaxed sm:text-xl {{ $subClass }}">{{ $sub }}</p>
                @endif

                @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                    <div class="flex flex-wrap gap-4 pt-2 {{ $actionsClass }}">
                        @if ($ctaLabel !== '' && $ctaUrl !== '')
                            <a
                                href="{{ $ctaUrl }}"
                                class="inline-flex items-center rounded-full px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 {{ $ctaClass }}">
                                {{ $ctaLabel }}
                            </a>
                        @endif

                        @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                            <a
                                href="{{ $secondaryUrl }}"
                                class="inline-flex items-center rounded-full border px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 {{ $hasBackground ? 'border-white/40 text-white/90 hover:bg-white/10 hover:text-white' : 'border-surface-300 text-surface-700 hover:border-surface-400 hover:bg-surface-50' }}">
                                {{ $secondaryLabel }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if ($bg !== '' && $splitLayout)
                <div class="overflow-hidden rounded-2xl border border-surface-200/80 bg-surface-100 shadow-lg">
                    <img src="{{ $bg }}" alt="" class="h-full w-full object-cover" />
                </div>
            @endif
        </div>
    </div>
</section>
