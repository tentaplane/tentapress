@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['embed_url'] ?? ''));
    $height = (int) ($props['height'] ?? 420);
    $caption = (string) ($props['caption'] ?? '');
    $border = filter_var($props['border'] ?? true, FILTER_VALIDATE_BOOL);

    $frameClass = $border ? 'border border-black/[0.08]' : '';
@endphp

@if ($url !== '')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-5 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold text-surface-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-[2.5rem] bg-white {{ $frameClass }}" style="height: {{ $height }}px">
                <iframe src="{{ $url }}" class="h-full w-full" loading="lazy"></iframe>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-surface-500">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
