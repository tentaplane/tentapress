@php
    $type = (string) ($type ?? 'unknown');
    $props = is_array($props ?? null) ? $props : [];
@endphp

@if ($type === 'blocks/content' && isset($props['content']) && is_string($props['content']))
    <div class="prose max-w-none">
        {!! nl2br(e($props['content'])) !!}
    </div>
@else
    <div class="rounded border border-black/10 bg-[#f6f7f7] p-4">
        <div class="text-xs font-semibold text-black/60 uppercase">Block</div>
        <div class="mt-1 font-mono text-sm text-black/80">{{ $type }}</div>

        @if (!empty($props))
            <pre class="mt-3 text-xs whitespace-pre-wrap text-black/70">
{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
            </pre>
        @endif
    </div>
@endif
