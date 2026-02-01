@php
    $quote = (string) ($props['quote'] ?? '');
    $name = (string) ($props['name'] ?? '');
    $role = (string) ($props['role'] ?? '');
    $avatar = (string) ($props['avatar'] ?? '');
    $rating = (int) ($props['rating'] ?? 0);
    $alignment = (string) ($props['alignment'] ?? 'left');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
@endphp

@if ($quote !== '')
    <section class="py-12">
        <div class="mx-auto max-w-4xl">
            <div class="flex flex-col gap-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm {{ $alignClass }}">
                <div class="flex gap-1 text-slate-900">
                    @for ($i = 0; $i < $rating; $i++)
                        <span>★</span>
                    @endfor
                </div>

                <p class="text-lg font-medium text-slate-900 md:text-xl">
                    “{{ $quote }}”
                </p>

                <div class="flex items-center gap-4">
                    @if ($avatar !== '')
                        <img src="{{ $avatar }}" alt="" class="h-12 w-12 rounded-full object-cover" />
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-500">
                            {{ mb_substr($name !== '' ? $name : 'TP', 0, 2) }}
                        </div>
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
    </section>
@endif
