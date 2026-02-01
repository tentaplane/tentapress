@php
    $eyebrow = (string) ($props['eyebrow'] ?? '');
    $headline = (string) ($props['headline'] ?? '');
    $sub = (string) ($props['subheadline'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $image = (string) ($props['background_image'] ?? '');
    $cta = is_array($props['primary_cta'] ?? null) ? $props['primary_cta'] : [];
    $ctaLabel = (string) ($cta['label'] ?? '');
    $ctaUrl = (string) ($cta['url'] ?? '');
    $ctaStyle = (string) ($cta['style'] ?? 'primary');
    $secondary = is_array($props['secondary_cta'] ?? null) ? $props['secondary_cta'] : [];
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';

    $ctaClass = match ($ctaStyle) {
        'outline' => 'border border-slate-300 text-slate-900 hover:border-slate-400',
        'ghost' => 'text-slate-600 hover:text-slate-900',
        default => 'bg-slate-900 text-white hover:bg-slate-800',
    };
@endphp

<section class="relative overflow-hidden">
    <div class="absolute -left-32 top-10 h-56 w-56 rounded-full bg-blue-500/10 blur-[80px]"></div>
    <div class="absolute right-0 top-24 h-72 w-72 rounded-full bg-indigo-500/10 blur-[90px]"></div>

    <div class="mx-auto max-w-6xl py-10">
        <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="flex flex-col gap-6 {{ $alignClass }}">
                @if ($eyebrow !== '')
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                        {{ $eyebrow }}
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    </div>
                @endif

                @if ($headline !== '')
                    <h1 class="text-4xl font-semibold leading-tight text-slate-900 md:text-5xl lg:text-6xl">
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
                        <a href="{{ $ctaUrl }}" class="inline-flex items-center rounded-full px-6 py-3 text-sm font-semibold shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 {{ $ctaClass }}">
                            {{ $ctaLabel }}
                        </a>
                    @endif

                    @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                        <a href="{{ $secondaryUrl }}" class="inline-flex items-center rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>

                <div class="flex flex-wrap gap-6 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                    <span>No credit card</span>
                    <span>Launch in days</span>
                    <span>Built on Laravel</span>
                </div>
            </div>

            <div class="relative">
                @if ($image !== '')
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                        <img src="{{ $image }}" alt="" class="h-full w-full object-cover" />
                    </div>
                @else
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-900/10">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                            <span>Editor preview</span>
                            <span>Live</span>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div class="h-3 w-3/4 rounded-full bg-slate-100"></div>
                            <div class="h-3 w-2/3 rounded-full bg-slate-100"></div>
                            <div class="h-24 rounded-2xl bg-gradient-to-br from-blue-500/15 via-indigo-500/10 to-slate-100"></div>
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
