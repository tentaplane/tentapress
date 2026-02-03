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
        'muted' => 'bg-slate-100/70 border border-slate-200/70',
        default => 'bg-white border border-slate-200/80 shadow-sm',
    };

    $panelPadding = $background === 'none' ? '' : 'p-6 sm:p-8';
@endphp

<section class="py-12 sm:py-16">
    <div class="mx-auto px-6 {{ $widthClass }}">
        <div class="rounded-2xl {{ $panelClass }} {{ $panelPadding }} {{ $alignClass }}">
            @if ($content !== '')
                <div class="prose max-w-none whitespace-pre-wrap text-slate-600">{!! nl2br(e($content)) !!}</div>
            @else
                <div class="text-sm text-slate-400">No content.</div>
            @endif
        </div>
    </div>
</section>
