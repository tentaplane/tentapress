{{-- tp:block
{
    "name": "Before / After Split",
    "description": "Interactive image comparison slider.",
    "version": 1,
    "fields": [
        { "key": "title", "label": "Title", "type": "text" },
        { "key": "before_image", "label": "Before Image", "type": "media" },
        { "key": "after_image", "label": "After Image", "type": "media" },
        { "key": "before_label", "label": "Before Label", "type": "text" },
        { "key": "after_label", "label": "After Label", "type": "text" },
        { "key": "start_percent", "label": "Start Percent", "type": "range", "min": 5, "max": 95, "step": 1 }
    ],
    "defaults": {
        "title": "Before & after",
        "before_image": "",
        "after_image": "",
        "before_label": "Before",
        "after_label": "After",
        "start_percent": 50
    }
}
--}}
@php
    $title = trim((string) ($props['title'] ?? ''));
    $beforeImage = trim((string) ($props['before_image'] ?? ''));
    $afterImage = trim((string) ($props['after_image'] ?? ''));
    $beforeLabel = trim((string) ($props['before_label'] ?? 'Before'));
    $afterLabel = trim((string) ($props['after_label'] ?? 'After'));

    $startPercent = (int) ($props['start_percent'] ?? 50);
    if ($startPercent < 5) {
        $startPercent = 5;
    }
    if ($startPercent > 95) {
        $startPercent = 95;
    }

    $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
    $beforeRef = null;
    $afterRef = null;
    if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
        if ($beforeImage !== '') {
            $beforeRef = $resolver->resolveImage(
                ['url' => $beforeImage, 'alt' => $beforeLabel],
                ['variant' => 'large', 'sizes' => '(min-width: 1024px) 960px, 100vw']
            );
        }
        if ($afterImage !== '') {
            $afterRef = $resolver->resolveImage(
                ['url' => $afterImage, 'alt' => $afterLabel],
                ['variant' => 'large', 'sizes' => '(min-width: 1024px) 960px, 100vw']
            );
        }
    }

    $beforeSrc = is_array($beforeRef) ? (string) ($beforeRef['src'] ?? '') : $beforeImage;
    $beforeSrcset = is_array($beforeRef) ? ($beforeRef['srcset'] ?? null) : null;
    $beforeSizes = is_array($beforeRef) ? ($beforeRef['sizes'] ?? null) : null;
    $afterSrc = is_array($afterRef) ? (string) ($afterRef['src'] ?? '') : $afterImage;
    $afterSrcset = is_array($afterRef) ? ($afterRef['srcset'] ?? null) : null;
    $afterSizes = is_array($afterRef) ? ($afterRef['sizes'] ?? null) : null;

    $instance = 'tp-before-after-'.substr(md5((string) json_encode([$beforeImage, $afterImage, $startPercent])), 0, 8);
@endphp

<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-5xl px-6">
        @if ($title !== '')
            <h2 class="mb-6 text-center font-display text-3xl font-semibold text-surface-900 sm:text-4xl">{{ $title }}</h2>
        @endif

        @if ($beforeSrc === '' || $afterSrc === '')
            <div class="rounded-2xl border border-dashed border-black/15 bg-white/70 p-6 text-center text-sm text-surface-500">
                Select both before and after images to use the comparison slider.
            </div>
        @else
            <div id="{{ $instance }}" class="rounded-2xl border border-black/8 bg-white p-4 sm:p-6">
                <div data-compare class="relative aspect-video overflow-hidden rounded-xl border border-black/10 bg-slate-100">
                    <img
                        src="{{ $beforeSrc }}"
                        alt="{{ $beforeLabel }}"
                        @if (is_string($beforeSrcset) && $beforeSrcset !== '') srcset="{{ $beforeSrcset }}" @endif
                        @if (is_string($beforeSizes) && $beforeSizes !== '') sizes="{{ $beforeSizes }}" @endif
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                        decoding="async" />

                    <img
                        data-after-image
                        src="{{ $afterSrc }}"
                        alt="{{ $afterLabel }}"
                        @if (is_string($afterSrcset) && $afterSrcset !== '') srcset="{{ $afterSrcset }}" @endif
                        @if (is_string($afterSizes) && $afterSizes !== '') sizes="{{ $afterSizes }}" @endif
                        class="absolute inset-0 h-full w-full object-cover"
                        style="clip-path: inset(0 calc(100% - {{ $startPercent }}%) 0 0);"
                        loading="lazy"
                        decoding="async" />

                    <div data-divider class="pointer-events-none absolute inset-y-0" style="left: {{ $startPercent }}%; transform: translateX(-1px);">
                        <div class="h-full w-0.5 bg-white shadow"></div>
                    </div>

                    <div class="pointer-events-none absolute left-3 top-3 rounded-full bg-black/65 px-2.5 py-1 text-xs font-semibold text-white">{{ $beforeLabel }}</div>
                    <div class="pointer-events-none absolute right-3 top-3 rounded-full bg-black/65 px-2.5 py-1 text-xs font-semibold text-white">{{ $afterLabel }}</div>
                </div>

                <div class="mt-4">
                    <input
                        data-range
                        type="range"
                        min="5"
                        max="95"
                        step="1"
                        value="{{ $startPercent }}"
                        class="w-full" />
                </div>
            </div>

            <script>
                (function () {
                    const root = document.getElementById(@json($instance));
                    if (!root) return;

                    const range = root.querySelector('[data-range]');
                    const compare = root.querySelector('[data-compare]');
                    const afterImage = root.querySelector('[data-after-image]');
                    const divider = root.querySelector('[data-divider]');
                    if (!range || !compare || !afterImage || !divider) return;

                    const apply = (value) => {
                        const pct = Math.max(5, Math.min(95, Number(value || 50)));
                        afterImage.style.clipPath = `inset(0 calc(100% - ${pct}%) 0 0)`;
                        divider.style.left = `${pct}%`;
                    };

                    apply(range.value);
                    range.addEventListener('input', () => apply(range.value));
                })();
            </script>
        @endif
    </div>
</section>
