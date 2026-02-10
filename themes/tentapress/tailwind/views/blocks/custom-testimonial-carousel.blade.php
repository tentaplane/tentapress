{{-- tp:block
{
    "name": "Testimonial Carousel",
    "description": "Slide-based testimonials with optional autoplay and avatars.",
    "version": 1,
    "fields": [
        { "key": "title", "label": "Title", "type": "text" },
        { "key": "testimonials", "label": "Testimonials JSON", "type": "textarea", "rows": 10, "help": "JSON array of {quote,name,role,avatar}." },
        { "key": "autoplay", "label": "Autoplay", "type": "toggle" },
        { "key": "interval_seconds", "label": "Interval (seconds)", "type": "number", "min": 2, "max": 20, "step": 1 },
        { "key": "show_avatars", "label": "Show avatars", "type": "toggle" }
    ],
    "defaults": {
        "title": "What customers say",
        "testimonials": [
            { "quote": "Switching to TentaPress cut our publishing time in half.", "name": "Avery Brooks", "role": "Marketing Lead", "avatar": "" },
            { "quote": "We ship new landing pages weekly without engineering bottlenecks.", "name": "Jordan Lee", "role": "Growth Manager", "avatar": "" },
            { "quote": "The block editor is clean, fast, and easy for every team member.", "name": "Sam Rivera", "role": "Content Strategist", "avatar": "" }
        ],
        "autoplay": true,
        "interval_seconds": 6,
        "show_avatars": true
    }
}
--}}
@php
    $title = trim((string) ($props['title'] ?? ''));
    $rawItems = $props['testimonials'] ?? [];

    if (is_string($rawItems)) {
        $trim = trim($rawItems);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
        $items = is_array($decoded) ? $decoded : [];
    } elseif (is_array($rawItems)) {
        $items = $rawItems;
    } else {
        $items = [];
    }

    $items = array_values(array_filter($items, static fn ($item): bool => is_array($item) && trim((string) ($item['quote'] ?? '')) !== ''));

    $autoplay = (bool) ($props['autoplay'] ?? true);
    $intervalSeconds = (int) ($props['interval_seconds'] ?? 6);
    if ($intervalSeconds < 2) {
        $intervalSeconds = 2;
    }
    if ($intervalSeconds > 20) {
        $intervalSeconds = 20;
    }

    $showAvatars = (bool) ($props['show_avatars'] ?? true);
    $carouselId = 'tp-testimonial-carousel-'.substr(md5((string) json_encode([$items, $autoplay, $intervalSeconds, $showAvatars])), 0, 8);
@endphp

<section class="py-14 sm:py-18">
    <div class="mx-auto max-w-5xl px-6">
        @if ($title !== '')
            <h2 class="mb-6 text-center font-display text-3xl font-semibold text-surface-900 sm:text-4xl">{{ $title }}</h2>
        @endif

        @if ($items === [])
            <div class="rounded-2xl border border-dashed border-black/15 bg-white/70 p-6 text-center text-sm text-surface-500">
                Add testimonial items in JSON to render the carousel.
            </div>
        @else
            <div id="{{ $carouselId }}" class="rounded-2xl border border-black/8 bg-white p-6 sm:p-8">
                <div class="relative overflow-hidden">
                    @foreach ($items as $idx => $item)
                        @php
                            $quote = trim((string) ($item['quote'] ?? ''));
                            $name = trim((string) ($item['name'] ?? ''));
                            $role = trim((string) ($item['role'] ?? ''));
                            $avatar = trim((string) ($item['avatar'] ?? ''));
                            $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
                            $avatarRef = null;
                            if ($avatar !== '' && is_object($resolver) && method_exists($resolver, 'resolveImage')) {
                                $avatarRef = $resolver->resolveImage(
                                    ['url' => $avatar, 'alt' => $name],
                                    ['variant' => 'thumb', 'sizes' => '40px']
                                );
                            }
                            $avatarSrc = is_array($avatarRef) ? (string) ($avatarRef['src'] ?? '') : $avatar;
                            $avatarAlt = is_array($avatarRef) ? (string) ($avatarRef['alt'] ?? $name) : $name;
                            $avatarSrcset = is_array($avatarRef) ? ($avatarRef['srcset'] ?? null) : null;
                            $avatarWidth = is_array($avatarRef) && isset($avatarRef['width']) && is_int($avatarRef['width']) ? $avatarRef['width'] : null;
                            $avatarHeight = is_array($avatarRef) && isset($avatarRef['height']) && is_int($avatarRef['height']) ? $avatarRef['height'] : null;
                        @endphp

                        <article data-slide class="{{ $idx === 0 ? 'block' : 'hidden' }} text-center">
                            <blockquote class="mx-auto max-w-3xl text-pretty text-lg leading-relaxed text-surface-700 sm:text-xl">
                                “{{ $quote }}”
                            </blockquote>

                            <div class="mt-6 flex items-center justify-center gap-3">
                                @if ($showAvatars && $avatarSrc !== '')
                                    <img
                                        src="{{ $avatarSrc }}"
                                        alt="{{ $avatarAlt }}"
                                        @if (is_string($avatarSrcset) && $avatarSrcset !== '') srcset="{{ $avatarSrcset }}" @endif
                                        sizes="40px"
                                        @if (is_int($avatarWidth) && $avatarWidth > 0) width="{{ $avatarWidth }}" @endif
                                        @if (is_int($avatarHeight) && $avatarHeight > 0) height="{{ $avatarHeight }}" @endif
                                        class="h-10 w-10 rounded-full object-cover"
                                        loading="lazy"
                                        decoding="async" />
                                @endif
                                <div>
                                    <div class="font-semibold text-surface-900">{{ $name !== '' ? $name : 'Anonymous' }}</div>
                                    @if ($role !== '')
                                        <div class="text-sm text-surface-500">{{ $role }}</div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if (count($items) > 1)
                    <div class="mt-6 flex items-center justify-center gap-2">
                        @foreach ($items as $idx => $item)
                            <button
                                type="button"
                                data-dot
                                data-index="{{ $idx }}"
                                class="h-2.5 w-2.5 rounded-full {{ $idx === 0 ? 'bg-surface-900' : 'bg-surface-300' }}"
                                aria-label="Go to slide {{ $idx + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if (count($items) > 1)
                <script>
                    (function () {
                        const root = document.getElementById(@json($carouselId));
                        if (!root) return;

                        const slides = Array.from(root.querySelectorAll('[data-slide]'));
                        const dots = Array.from(root.querySelectorAll('[data-dot]'));
                        if (!slides.length) return;

                        let index = 0;
                        let timer = null;
                        const autoplay = @json($autoplay);
                        const intervalMs = @json($intervalSeconds * 1000);

                        const show = (next) => {
                            index = (next + slides.length) % slides.length;
                            slides.forEach((slide, i) => {
                                slide.classList.toggle('hidden', i !== index);
                                slide.classList.toggle('block', i === index);
                            });
                            dots.forEach((dot, i) => {
                                dot.classList.toggle('bg-surface-900', i === index);
                                dot.classList.toggle('bg-surface-300', i !== index);
                            });
                        };

                        dots.forEach((dot) => {
                            dot.addEventListener('click', () => {
                                const next = Number(dot.getAttribute('data-index') || 0);
                                show(next);
                            });
                        });

                        if (autoplay) {
                            timer = window.setInterval(() => show(index + 1), intervalMs);
                            root.addEventListener('mouseenter', () => {
                                if (timer) window.clearInterval(timer);
                            });
                            root.addEventListener('mouseleave', () => {
                                timer = window.setInterval(() => show(index + 1), intervalMs);
                            });
                        }
                    })();
                </script>
            @endif
        @endif
    </div>
</section>
