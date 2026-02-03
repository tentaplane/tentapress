@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['url'] ?? ''));
    $aspect = (string) ($props['aspect'] ?? '16:9');
    $height = (int) ($props['height'] ?? 480);
    $allowFullscreen = filter_var($props['allow_fullscreen'] ?? true, FILTER_VALIDATE_BOOL);
    $caption = (string) ($props['caption'] ?? '');

    $aspectClass = match ($aspect) {
        'square' => 'aspect-square',
        '4:3' => 'aspect-[4/3]',
        '16:9' => 'aspect-video',
        default => '',
    };
@endphp

@if ($url !== '')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-5 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-lg shadow-slate-200/60">
                <div class="w-full {{ $aspectClass }}" @if ($aspect === 'auto') style="height: {{ $height }}px" @endif>
                    <iframe
                        src="{{ $url }}"
                        class="h-full w-full"
                        loading="lazy"
                        @if ($allowFullscreen) allowfullscreen @endif></iframe>
                </div>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-slate-500">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
