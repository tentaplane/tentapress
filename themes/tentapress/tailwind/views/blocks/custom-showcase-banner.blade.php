{{-- tp:block
{
    "name": "Showcase Banner",
    "description": "A promotional banner with headline, body copy, and call to action.",
    "version": 1,
    "fields": [
        { "key": "eyebrow", "label": "Eyebrow", "type": "text" },
        { "key": "headline", "label": "Headline", "type": "text" },
        { "key": "body", "label": "Body", "type": "textarea", "rows": 4 },
        { "key": "cta_label", "label": "Button Label", "type": "text" },
        { "key": "cta_url", "label": "Button URL", "type": "text" },
        {
            "key": "tone",
            "label": "Tone",
            "type": "select",
            "options": [
                { "value": "sand", "label": "Sand" },
                { "value": "sky", "label": "Sky" },
                { "value": "graphite", "label": "Graphite" }
            ]
        },
        {
            "key": "alignment",
            "label": "Alignment",
            "type": "select",
            "options": [
                { "value": "left", "label": "Left" },
                { "value": "center", "label": "Center" }
            ]
        }
    ],
    "defaults": {
        "eyebrow": "New",
        "headline": "Build your next launch page in minutes.",
        "body": "Create sections, reorder fast, and publish with confidence using TentaPress blocks.",
        "cta_label": "Start now",
        "cta_url": "#",
        "tone": "sand",
        "alignment": "left"
    },
    "example": {
        "props": {
            "eyebrow": "Demo",
            "headline": "A custom block from a single theme file.",
            "body": "This block is discovered automatically by the custom-blocks plugin.",
            "cta_label": "See docs",
            "cta_url": "https://tentapress.com/docs",
            "tone": "sky",
            "alignment": "center"
        }
    }
}
--}}
@php
    $eyebrow = trim((string) ($props['eyebrow'] ?? ''));
    $headline = trim((string) ($props['headline'] ?? ''));
    $body = trim((string) ($props['body'] ?? ''));
    $ctaLabel = trim((string) ($props['cta_label'] ?? ''));
    $ctaUrl = trim((string) ($props['cta_url'] ?? ''));
    $tone = trim((string) ($props['tone'] ?? 'sand'));
    $alignment = trim((string) ($props['alignment'] ?? 'left'));

    $containerClasses = match ($tone) {
        'sky' => 'border-sky-200 bg-gradient-to-br from-sky-50 to-cyan-100',
        'graphite' => 'border-slate-700 bg-gradient-to-br from-slate-900 to-slate-700',
        default => 'border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100',
    };

    $isDarkTone = $tone === 'graphite';
    $headingClasses = $isDarkTone ? 'text-white' : 'text-surface-900';
    $textClasses = $isDarkTone ? 'text-white/80' : 'text-surface-700';
    $eyebrowClasses = $isDarkTone ? 'text-white/70' : 'text-surface-500';
    $buttonClasses = $isDarkTone
        ? 'bg-white text-slate-900 hover:bg-white/90'
        : 'bg-surface-900 text-white hover:opacity-85';
    $alignClasses = $alignment === 'center' ? 'items-center text-center' : 'items-start text-left';
@endphp

<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-6xl px-6">
        <div class="rounded-4xl border p-8 shadow-sm sm:p-12 {{ $containerClasses }}">
            <div class="flex flex-col gap-5 {{ $alignClasses }}">
                @if ($eyebrow !== '')
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] {{ $eyebrowClasses }}">{{ $eyebrow }}</p>
                @endif

                @if ($headline !== '')
                    <h2 class="max-w-3xl text-balance font-display text-3xl font-semibold sm:text-5xl {{ $headingClasses }}">
                        {{ $headline }}
                    </h2>
                @endif

                @if ($body !== '')
                    <p class="max-w-2xl text-pretty text-base leading-relaxed sm:text-lg {{ $textClasses }}">
                        {{ $body }}
                    </p>
                @endif

                @if ($ctaLabel !== '' && $ctaUrl !== '')
                    <a
                        href="{{ $ctaUrl }}"
                        class="mt-1 inline-flex items-center rounded-lg px-6 py-3 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $buttonClasses }}">
                        {{ $ctaLabel }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
