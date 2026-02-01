@php
    $title = (string) ($props['title'] ?? '');
    $body = (string) ($props['body'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');
    $button = is_array($props['button'] ?? null) ? $props['button'] : [];
    $buttonLabel = (string) ($button['label'] ?? '');
    $buttonUrl = (string) ($button['url'] ?? '');
    $buttonStyle = (string) ($button['style'] ?? 'primary');
    $secondary = is_array($props['secondary_button'] ?? null) ? $props['secondary_button'] : [];
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';

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
            <div class="flex flex-col gap-6 rounded-3xl border border-slate-200/70 px-8 py-10 shadow-sm {{ $panelClass }} {{ $alignClass }}">
                @if ($title !== '')
                    <h2 class="text-3xl font-semibold md:text-4xl">{{ $title }}</h2>
                @endif
                @if ($body !== '')
                    <p class="text-base {{ $background === 'muted' ? 'text-white/80' : 'text-slate-600' }} md:text-lg">
                        {{ $body }}
                    </p>
                @endif
                <div class="flex flex-wrap items-center gap-4">
                    @if ($buttonLabel !== '' && $buttonUrl !== '')
                        <a href="{{ $buttonUrl }}" class="inline-flex items-center rounded-full px-6 py-3 text-sm font-semibold shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 {{ $ctaClass }}">
                            {{ $buttonLabel }}
                        </a>
                    @endif
                    @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                        <a href="{{ $secondaryUrl }}" class="inline-flex items-center rounded-full border border-white/20 px-5 py-3 text-sm font-semibold {{ $background === 'muted' ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ $secondaryLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
