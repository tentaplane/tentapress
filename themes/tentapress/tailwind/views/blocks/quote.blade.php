@php
    $quote = (string) ($props['quote'] ?? '');
    $name = (string) ($props['name'] ?? '');
    $role = (string) ($props['role'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $style = (string) ($props['style'] ?? 'simple');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $panelClass = $style === 'card'
        ? 'rounded-[2.5rem] border border-black/[0.08] bg-white p-10 sm:p-14'
        : 'border-l-4 border-surface-900 pl-8';
@endphp

@if ($quote !== '')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-4xl px-6">
            <div class="flex flex-col gap-5 {{ $panelClass }} {{ $alignClass }}">
                <blockquote class="text-pretty font-display text-2xl font-medium text-surface-800 sm:text-3xl">"{{ $quote }}"</blockquote>
                @if ($name !== '' || $role !== '')
                    <div class="text-sm text-surface-500">
                        {{ $name }}
                        @if ($name !== '' && $role !== '')
                            <span class="mx-1">·</span>
                        @endif
                        {{ $role }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
