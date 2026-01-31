@php
    $quote = (string) ($props['quote'] ?? '');
    $name = (string) ($props['name'] ?? '');
    $role = (string) ($props['role'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $style = (string) ($props['style'] ?? 'simple');

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';
    $panelClass = $style === 'card' ? 'rounded-xl border border-black/10 bg-white p-8' : '';
@endphp

@if ($quote !== '')
    <section class="py-10">
        <div class="mx-auto max-w-4xl px-6">
            <div class="{{ $panelClass }} {{ $alignClass }}">
                <blockquote class="text-lg text-black/80">“{{ $quote }}”</blockquote>
                @if ($name !== '' || $role !== '')
                    <div class="mt-4 text-sm text-black/60">
                        {{ $name }}
                        @if ($name !== '' && $role !== '')
                            <span>·</span>
                        @endif
                        {{ $role }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
