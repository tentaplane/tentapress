@php
    $content = (string) ($props['content'] ?? '');
    $width = (string) ($props['width'] ?? 'normal');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');

    $widthClass = match ($width) {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-6xl',
        default => 'max-w-5xl',
    };

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';

    $panelClass = match ($background) {
        'none' => 'bg-transparent',
        'muted' => 'bg-slate-50 border border-black/5',
        default => 'bg-white border border-black/10',
    };

    $panelPadding = $background === 'none' ? '' : 'p-8';
@endphp

<section class="py-10">
    <div class="mx-auto px-6 {{ $widthClass }}">
        <div class="rounded-xl {{ $panelClass }} {{ $panelPadding }} {{ $alignClass }}">
            @if ($content !== '')
                <div class="prose max-w-none">{!! $content !!}</div>
            @else
                <div class="text-black/50">No content.</div>
            @endif
        </div>
    </div>
</section>
