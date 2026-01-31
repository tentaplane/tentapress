@php
    $blocks = is_array($blocks ?? null) ? $blocks : [];
@endphp

@if (count($blocks) === 0)
    <div class="text-sm text-black/60">This page has no blocks yet.</div>
@else
    <div class="space-y-6">
        @foreach ($blocks as $block)
            @php
                $type = is_array($block) && isset($block['type']) ? (string) $block['type'] : 'unknown';
                $props = is_array($block) && isset($block['props']) && is_array($block['props']) ? $block['props'] : [];
            @endphp

            @include('tentapress-pages::blocks.fallback-single', [
                'type' => $type,
                'props' => $props,
            ])
        @endforeach
    </div>
@endif
