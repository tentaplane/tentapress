@php
    $content = (string) ($props['content'] ?? '');
    $width = (string) ($props['width'] ?? 'normal');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');

    $widthClass = match ($width) {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-6xl',
        default => 'max-w-4xl',
    };

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';

    $panelClass = match ($background) {
        'none' => 'bg-transparent',
        'muted' => 'bg-surface-100/80 border border-surface-200/80',
        default => 'bg-white border border-surface-200 shadow-sm',
    };

    $panelPadding = $background === 'none' ? '' : 'p-6 sm:p-10';
@endphp

<section class="py-14 sm:py-20">
    <div class="mx-auto px-6 {{ $widthClass }}">
        <div class="rounded-xl {{ $panelClass }} {{ $panelPadding }} {{ $alignClass }}">
            @if ($content !== '')
                <div class="prose prose-surface max-w-none whitespace-pre-wrap text-surface-700 prose-headings:font-display prose-headings:text-surface-900 prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline">{!! nl2br(e($content)) !!}</div>
            @else
                <div class="text-sm text-surface-400">No content.</div>
            @endif
        </div>
    </div>
</section>
