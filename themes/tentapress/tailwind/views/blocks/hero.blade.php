@php
    $eyebrow = (string) ($props['eyebrow'] ?? '');
    $headline = (string) ($props['headline'] ?? '');
    $sub = (string) ($props['subheadline'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $image = (string) ($props['background_image'] ?? '');
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

    $alignClass = $alignment === 'center' ? 'items-center text-center' : 'items-start text-left';

    $ctaClass = match ($ctaStyle) {
        'outline' => 'border border-slate-300 text-slate-900 hover:border-slate-400',
        'ghost' => 'text-slate-600 hover:text-slate-900',
        default => 'bg-slate-900 text-white hover:bg-slate-800',
    };
@endphp

<section class="relative overflow-hidden">
    <div class="absolute top-10 -left-32 h-56 w-56 rounded-full bg-blue-500/10 blur-[80px]"></div>
    <div class="absolute top-24 right-0 h-72 w-72 rounded-full bg-indigo-500/10 blur-[90px]"></div>

    <div class="mx-auto max-w-6xl py-10">
        <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="{{ $alignClass }} flex flex-col gap-6">
                @if ($eyebrow !== '')
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1 text-xs font-semibold tracking-[0.2em] text-slate-500 uppercase">
                        {{ $eyebrow }}
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    </div>
                @endif

                @if ($headline !== '')
                    <h1 class="text-4xl leading-tight font-semibold text-slate-900 md:text-5xl lg:text-6xl">
                        {!! nl2br(e($headline)) !!}
                    </h1>
                @endif

                @if ($sub !== '')
                    <p class="text-base text-slate-600 md:text-lg">
                        {!! nl2br(e($sub)) !!}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-4">
                    @if ($ctaLabel !== '' && $ctaUrl !== '')
                        <a
                            href="{{ $ctaUrl }}"
                            class="{{ $ctaClass }} inline-flex items-center rounded-full px-6 py-3 text-sm font-semibold shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5">
                            {{ $ctaLabel }}
                        </a>
                    @endif

                    @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                        <a
                            href="{{ $secondaryUrl }}"
                            class="inline-flex items-center rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>

                <div class="flex flex-wrap gap-6 text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase">
                    <span>No credit card</span>
                    <span>Launch in days</span>
                    <span>Built on Laravel</span>
                </div>
            </div>

            <div class="relative">
                @if ($image !== '')
                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                        <img src="{{ $image }}" alt="" class="h-full w-full object-cover" />
                    </div>
                @else
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/10">
                        <div
                            class="flex items-center justify-between text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase">
                            <span>Editor preview</span>
                            <span>Live</span>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div class="h-3 w-3/4 rounded-full bg-slate-100"></div>
                            <div class="h-3 w-2/3 rounded-full bg-slate-100"></div>
                            <div
                                class="h-24 rounded-2xl bg-gradient-to-br from-blue-500/15 via-indigo-500/10 to-slate-100"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="h-20 rounded-2xl bg-slate-100"></div>
                                <div class="h-20 rounded-2xl bg-slate-100"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
