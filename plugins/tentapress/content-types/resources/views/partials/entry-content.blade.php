@php
    $visibleFields = array_values(array_filter($presentedFields, fn (array $field): bool => trim((string) ($field['display'] ?? '')) !== ''));
@endphp

@if ($blocksHtml !== '')
    <div>{!! $blocksHtml !!}</div>
@endif

@if ($visibleFields !== [])
    <section class="space-y-4 pt-8">
        @foreach ($visibleFields as $field)
            <article class="rounded-3xl border border-black/10 bg-white/70 p-5 shadow-sm">
                <div class="text-sm font-medium uppercase tracking-[0.18em] text-black/55">{{ $field['label'] }}</div>
                <div class="pt-2 text-base text-black/85">{{ $field['display'] }}</div>
            </article>
        @endforeach
    </section>
@endif
