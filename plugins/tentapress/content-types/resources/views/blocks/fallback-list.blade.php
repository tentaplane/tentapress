@php
    $blocks = is_array($blocks ?? null) ? $blocks : [];
@endphp

@if ($blocks === [])
    <div class="text-sm text-black/60">No body content has been added yet.</div>
@else
    <div class="space-y-4">
        @foreach ($blocks as $block)
            @php
                $blockType = trim((string) ($block['type'] ?? 'Block'));
                $props = is_array($block['props'] ?? null) ? $block['props'] : [];
            @endphp

            <section class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="text-sm font-medium text-black">{{ $blockType }}</div>
                @if (($props['content'] ?? null) && is_string($props['content']))
                    <div class="mt-2 text-sm text-black/70">{{ $props['content'] }}</div>
                @else
                    <pre class="mt-2 overflow-x-auto text-xs text-black/60">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                @endif
            </section>
        @endforeach
    </div>
@endif
