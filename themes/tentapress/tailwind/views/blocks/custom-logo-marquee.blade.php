{{-- tp:block
{
    "name": "Logo Marquee",
    "description": "Animated strip of brand logos with optional grayscale and pause-on-hover.",
    "version": 1,
    "fields": [
        { "key": "title", "label": "Title", "type": "text" },
        { "key": "logos", "label": "Logos", "type": "media-list" },
        { "key": "speed_seconds", "label": "Speed (seconds)", "type": "number", "min": 8, "max": 60, "step": 1 },
        { "key": "pause_on_hover", "label": "Pause on hover", "type": "toggle" },
        { "key": "grayscale", "label": "Grayscale logos", "type": "toggle" }
    ],
    "defaults": {
        "title": "Trusted by teams worldwide",
        "logos": [],
        "speed_seconds": 24,
        "pause_on_hover": true,
        "grayscale": true
    }
}
--}}
@php
    $title = trim((string) ($props['title'] ?? ''));
    $logos = $props['logos'] ?? [];
    if (! is_array($logos)) {
        $logos = [];
    }
    $logos = array_values(array_filter(array_map(static fn ($url): string => trim((string) $url), $logos), static fn ($url): bool => $url !== ''));

    $speed = (int) ($props['speed_seconds'] ?? 24);
    if ($speed < 8) {
        $speed = 8;
    }
    if ($speed > 60) {
        $speed = 60;
    }

    $pauseOnHover = (bool) ($props['pause_on_hover'] ?? true);
    $grayscale = (bool) ($props['grayscale'] ?? true);
    $instance = 'tp-logo-marquee-'.substr(md5((string) json_encode([$logos, $speed, $pauseOnHover, $grayscale])), 0, 8);

    $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
    $logoItems = [];
    foreach ($logos as $logoUrl) {
        $resolved = null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $resolved = $resolver->resolveImage(
                ['url' => $logoUrl, 'alt' => ''],
                ['variant' => 'medium', 'sizes' => '160px']
            );
        }

        $logoItems[] = [
            'src' => is_array($resolved) ? (string) ($resolved['src'] ?? '') : $logoUrl,
            'srcset' => is_array($resolved) ? ($resolved['srcset'] ?? null) : null,
            'sizes' => is_array($resolved) ? ($resolved['sizes'] ?? null) : null,
            'width' => is_array($resolved) && isset($resolved['width']) && is_int($resolved['width']) ? $resolved['width'] : null,
            'height' => is_array($resolved) && isset($resolved['height']) && is_int($resolved['height']) ? $resolved['height'] : null,
        ];
    }

    $marqueeItems = $logoItems === [] ? [] : array_merge($logoItems, $logoItems);
@endphp

<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-6xl px-6">
        @if ($title !== '')
            <h2 class="mb-6 text-center font-display text-2xl font-semibold text-surface-900 sm:text-3xl">{{ $title }}</h2>
        @endif

        @if ($logos === [])
            <div class="rounded-2xl border border-dashed border-black/15 bg-white/70 p-6 text-center text-sm text-surface-500">
                Add logo images to start the marquee.
            </div>
        @else
            <div class="relative overflow-hidden rounded-2xl border border-black/8 bg-white py-6">
                <div class="{{ $instance }} flex w-max items-center gap-10 px-8">
                    @foreach ($marqueeItems as $logo)
                        <div class="flex h-12 w-32 shrink-0 items-center justify-center">
                            <img
                                src="{{ $logo['src'] }}"
                                alt=""
                                @if (is_string($logo['srcset'] ?? null) && ($logo['srcset'] ?? '') !== '') srcset="{{ $logo['srcset'] }}" @endif
                                @if (is_string($logo['sizes'] ?? null) && ($logo['sizes'] ?? '') !== '') sizes="{{ $logo['sizes'] }}" @endif
                                @if (is_int($logo['width'] ?? null) && ($logo['width'] ?? 0) > 0) width="{{ $logo['width'] }}" @endif
                                @if (is_int($logo['height'] ?? null) && ($logo['height'] ?? 0) > 0) height="{{ $logo['height'] }}" @endif
                                class="max-h-10 w-auto object-contain {{ $grayscale ? 'grayscale opacity-70' : 'opacity-95' }}"
                                loading="lazy"
                                decoding="async" />
                        </div>
                    @endforeach
                </div>
            </div>

            <style>
                .{{ $instance }} {
                    animation: {{ $instance }}-scroll {{ $speed }}s linear infinite;
                    will-change: transform;
                }
                @keyframes {{ $instance }}-scroll {
                    from { transform: translateX(0); }
                    to { transform: translateX(-50%); }
                }
                @if ($pauseOnHover)
                .{{ $instance }}:hover {
                    animation-play-state: paused;
                }
                @endif
            </style>
        @endif
    </div>
</section>
