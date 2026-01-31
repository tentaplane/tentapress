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
    <section class="py-10">
        <div class="mx-auto max-w-5xl space-y-3 px-6">
            @if ($title !== '')
                <h2 class="text-xl font-semibold">{{ $title }}</h2>
            @endif
            <div class="overflow-hidden rounded-xl border border-black/10 bg-white">
                <div class="w-full {{ $aspectClass }}" @if ($aspect === 'auto') style="height: {{ $height }}px" @endif>
                    <iframe
                        src="{{ $url }}"
                        class="h-full w-full"
                        loading="lazy"
                        @if ($allowFullscreen) allowfullscreen @endif></iframe>
                </div>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-black/60">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
