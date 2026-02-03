@php
    $quote = (string) ($props['quote'] ?? '');
    $name = (string) ($props['name'] ?? '');
    $role = (string) ($props['role'] ?? '');
    $avatar = (string) ($props['avatar'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $style = (string) ($props['style'] ?? 'card');
    $rating = (int) ($props['rating'] ?? 0);

    if ($rating < 0) {
        $rating = 0;
    } elseif ($rating > 5) {
        $rating = 5;
    }

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $metaClass = $alignment === 'center' ? 'flex-col items-center text-center' : 'items-center';
    $panelClass = $style === 'simple'
        ? 'bg-transparent'
        : 'rounded-[2.5rem] border border-slate-200/80 bg-white p-10 shadow-lg shadow-slate-200/60 sm:p-12';
@endphp

<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-6">
        <div class="relative overflow-hidden {{ $panelClass }} {{ $alignClass }}">
            @if ($style !== 'simple')
                <div class="pointer-events-none absolute -left-12 top-6 h-32 w-32 rounded-full bg-brand-100/80 blur-[100px]"></div>
            @endif

            <div class="relative flex flex-col gap-5">
                @if ($quote !== '')
                    <blockquote class="text-pretty text-xl font-medium text-slate-700 sm:text-2xl">
                        “{{ $quote }}”
                    </blockquote>
                @endif

                @if ($rating > 0)
                    <div class="flex items-center gap-1 text-sm text-brand-600 {{ $alignment === 'center' ? 'justify-center' : '' }}">
                        @for ($i = 0; $i < $rating; $i++)
                            <span>&#9733;</span>
                        @endfor
                    </div>
                @endif

                <div class="flex gap-4 {{ $metaClass }}">
                    @if ($avatar !== '')
                        <img src="{{ $avatar }}" alt="" class="h-12 w-12 rounded-full border border-slate-200/80 object-cover" />
                    @endif

                    <div>
                        @if ($name !== '')
                            <div class="text-sm font-semibold text-slate-900">{{ $name }}</div>
                        @endif

                        @if ($role !== '')
                            <div class="text-sm text-slate-500">{{ $role }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
