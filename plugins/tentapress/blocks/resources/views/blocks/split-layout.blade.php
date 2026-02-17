@php
    $ratio = trim((string) ($props['ratio'] ?? '50-50'));
    $gap = trim((string) ($props['gap'] ?? 'md'));
    $stackOnMobile = filter_var($props['stack_on_mobile'] ?? true, FILTER_VALIDATE_BOOL);
    $leftBlocks = is_array($props['left_blocks'] ?? null) ? $props['left_blocks'] : [];
    $rightBlocks = is_array($props['right_blocks'] ?? null) ? $props['right_blocks'] : [];

    $ratioClass = match ($ratio) {
        '60-40' => 'lg:grid-cols-[3fr_2fr]',
        '40-60' => 'lg:grid-cols-[2fr_3fr]',
        default => 'lg:grid-cols-2',
    };

    $gapClass = match ($gap) {
        'sm' => 'gap-4',
        'lg' => 'gap-8',
        default => 'gap-6',
    };

    $mobileClass = $stackOnMobile ? 'grid-cols-1' : 'grid-cols-2';
    $childRenderer = isset($renderBlocks) && is_callable($renderBlocks) ? $renderBlocks : null;
    $leftHtml = $childRenderer ? $childRenderer($leftBlocks) : '';
    $rightHtml = $childRenderer ? $childRenderer($rightBlocks) : '';
@endphp

<section class="py-12">
    <div class="mx-auto max-w-6xl px-6">
        <div class="grid {{ $mobileClass }} {{ $ratioClass }} {{ $gapClass }}">
            <div class="space-y-4">
                {!! $leftHtml !!}
            </div>
            <div class="space-y-4">
                {!! $rightHtml !!}
            </div>
        </div>
    </div>
</section>
