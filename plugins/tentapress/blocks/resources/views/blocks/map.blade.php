@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['embed_url'] ?? ''));
    $height = (int) ($props['height'] ?? 420);
    $caption = (string) ($props['caption'] ?? '');
    $border = filter_var($props['border'] ?? true, FILTER_VALIDATE_BOOL);

    $frameClass = $border ? 'border border-black/10' : '';
@endphp

@if ($url !== '')
    <section class="py-10">
        <div class="mx-auto max-w-5xl space-y-3 px-6">
            @if ($title !== '')
                <h2 class="text-xl font-semibold">{{ $title }}</h2>
            @endif
            <div class="overflow-hidden rounded-xl bg-white {{ $frameClass }}" style="height: {{ $height }}px">
                <iframe src="{{ $url }}" class="h-full w-full" loading="lazy"></iframe>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-black/60">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
