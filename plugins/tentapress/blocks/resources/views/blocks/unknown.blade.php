@php
    $type = (string) ($type ?? 'unknown');
    $props = is_array($props ?? null) ? $props : [];
@endphp

<section class="mb-6 rounded border border-black/10 bg-[#f6f7f7] p-4">
    <div class="text-xs font-semibold text-black/60 uppercase">Unknown block</div>
    <div class="mt-1 font-mono text-sm text-black/80">{{ $type }}</div>

    @if (!empty($props))
        <pre class="mt-3 text-xs whitespace-pre-wrap text-black/70">
{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre
        >
    @endif
</section>
