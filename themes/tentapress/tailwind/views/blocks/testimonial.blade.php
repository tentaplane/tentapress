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
        : 'rounded-[2.5rem] border border-black/[0.08] bg-white p-10 sm:p-14';

    $avatarRef = null;
    if ($avatar !== '') {
        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $avatarRef = $resolver->resolveImage(
                ['url' => $avatar, 'alt' => $name],
                ['variant' => 'thumb', 'sizes' => '48px']
            );
        }
    }

    $avatarSrc = is_array($avatarRef) ? (string) ($avatarRef['src'] ?? '') : $avatar;
    $avatarAlt = is_array($avatarRef) ? (string) ($avatarRef['alt'] ?? $name) : $name;
    $avatarSrcset = is_array($avatarRef) ? ($avatarRef['srcset'] ?? null) : null;
@endphp

<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-6">
        <div class="{{ $panelClass }} {{ $alignClass }}">
            <div class="flex flex-col gap-6">
                @if ($quote !== '')
                    <blockquote class="text-pretty text-xl font-medium leading-relaxed text-surface-800 sm:text-2xl">
                        "{{ $quote }}"
                    </blockquote>
                @endif

                @if ($rating > 0)
                    <div class="flex items-center gap-1 text-accent-500 {{ $alignment === 'center' ? 'justify-center' : '' }}">
                        @for ($i = 0; $i < $rating; $i++)
                            <span>&#9733;</span>
                        @endfor
                    </div>
                @endif

                <div class="flex gap-4 {{ $metaClass }}">
                    @if ($avatarSrc !== '')
                        <img
                            src="{{ $avatarSrc }}"
                            alt="{{ $avatarAlt }}"
                            @if (is_string($avatarSrcset) && $avatarSrcset !== '') srcset="{{ $avatarSrcset }}" @endif
                            sizes="48px"
                            class="h-12 w-12 rounded-full border-2 border-surface-100 object-cover"
                            loading="lazy"
                            decoding="async" />
                    @endif

                    <div>
                        @if ($name !== '')
                            <div class="text-sm font-semibold text-surface-900">{{ $name }}</div>
                        @endif

                        @if ($role !== '')
                            <div class="text-sm text-surface-500">{{ $role }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
