@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['embed_url'] ?? ''));
    $height = (int) ($props['height'] ?? 420);
    $caption = (string) ($props['caption'] ?? '');
    $border = filter_var($props['border'] ?? true, FILTER_VALIDATE_BOOL);

    $frameClass = $border ? 'border border-slate-200/80' : '';
@endphp

@if ($url !== '')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-5 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-3xl bg-white shadow-lg shadow-slate-200/60 {{ $frameClass }}" style="height: {{ $height }}px">
                <iframe src="{{ $url }}" class="h-full w-full" loading="lazy"></iframe>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-slate-500">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
