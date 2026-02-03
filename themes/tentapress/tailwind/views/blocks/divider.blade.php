@php
    $height = (int) ($props['height'] ?? 24);
    if ($height < 0) {
        $height = 0;
    }
    if ($height > 200) {
        $height = 200;
    }

    $label = trim((string) ($props['label'] ?? ''));
    $style = (string) ($props['style'] ?? 'line');

    $lineClass = match ($style) {
        'dashed' => 'border-t border-dashed border-slate-200',
        'none' => '',
        default => 'border-t border-slate-200',
    };
@endphp

<section class="py-8">
    <div class="mx-auto max-w-7xl px-6">
        <div style="height: {{ $height }}px" class="flex items-center justify-center">
            @if ($style !== 'none')
                <div class="w-full {{ $lineClass }}"></div>
            @endif
            @if ($label !== '')
                <span class="mx-4 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ $label }}</span>
            @endif
        </div>
    </div>
</section>
