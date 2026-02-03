@php
    $quote = (string) ($props['quote'] ?? '');
    $name = (string) ($props['name'] ?? '');
    $role = (string) ($props['role'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $style = (string) ($props['style'] ?? 'simple');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $panelClass = $style === 'card'
        ? 'rounded-3xl border border-slate-200/80 bg-white p-8 shadow-sm sm:p-10'
        : '';
@endphp

@if ($quote !== '')
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-5xl px-6">
            <div class="flex flex-col gap-4 {{ $panelClass }} {{ $alignClass }}">
                <blockquote class="text-pretty text-xl font-medium text-slate-700 sm:text-2xl">“{{ $quote }}”</blockquote>
                @if ($name !== '' || $role !== '')
                    <div class="text-sm text-slate-500">
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
