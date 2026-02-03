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
    $layoutClass = $splitLayout ? 'grid gap-10 lg:grid-cols-2 lg:items-center' : 'space-y-6';

    $ctaClass = match ($ctaStyle) {
        'outline' => 'border border-slate-200 text-slate-700',
        'ghost' => 'text-slate-500 hover:text-slate-900',
        default => 'bg-brand-600 text-white shadow-lg shadow-brand-600/30',
    };
@endphp

<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-8 shadow-sm sm:p-12">
            <div class="pointer-events-none absolute -left-20 top-0 h-40 w-40 rounded-full bg-brand-100/70 blur-[90px]"></div>
            <div class="pointer-events-none absolute -right-20 top-10 h-48 w-48 rounded-full bg-indigo-200/40 blur-[110px]"></div>
            <div class="relative {{ $layoutClass }} {{ $alignClass }}">
                @if ($bg !== '' && ! $splitLayout)
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100">
                        <img src="{{ $bg }}" alt="" class="h-auto w-full object-cover" />
                    </div>
                @endif

                <div class="space-y-5">
                    @if ($eyebrow !== '')
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if ($headline !== '')
                        <h1 class="font-display text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            {{ $headline }}
                        </h1>
                    @endif

                    @if ($sub !== '')
                        <p class="text-pretty text-base text-slate-500 sm:text-lg">{{ $sub }}</p>
                    @endif

                    @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                        <div class="mt-2 flex flex-wrap gap-3 {{ $actionsClass }}">
                            @if ($ctaLabel !== '' && $ctaUrl !== '')
                                <a
                                    href="{{ $ctaUrl }}"
                                    class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold {{ $ctaClass }}">
                                    {{ $ctaLabel }}
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

                @if ($bg !== '' && $splitLayout)
                    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100">
                        <img src="{{ $bg }}" alt="" class="h-auto w-full object-cover" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
