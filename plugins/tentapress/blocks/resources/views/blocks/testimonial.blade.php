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

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';
    $metaClass = $alignment === 'center' ? 'flex-col items-center' : 'items-center';
    $panelClass = $style === 'simple' ? 'bg-transparent' : 'rounded-xl border border-black/10 bg-white p-8';

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

<section class="py-10">
    <div class="mx-auto max-w-5xl px-6">
        <div class="{{ $panelClass }} {{ $alignClass }}">
            @if ($quote !== '')
                <blockquote class="text-lg whitespace-pre-wrap text-black/80">“{{ $quote }}”</blockquote>
            @endif

            @if ($rating > 0)
                <div class="mt-4 flex items-center gap-1 text-sm text-black/60 {{ $alignment === 'center' ? 'justify-center' : '' }}">
                    @for ($i = 0; $i < $rating; $i++)
                        <span>&#9733;</span>
                    @endfor
                </div>
            @endif

            <div class="mt-6 flex gap-4 {{ $metaClass }}">
                @if ($avatarSrc !== '')
                    <img
                        src="{{ $avatarSrc }}"
                        alt="{{ $avatarAlt }}"
                        @if (is_string($avatarSrcset) && $avatarSrcset !== '') srcset="{{ $avatarSrcset }}" @endif
                        sizes="48px"
                        class="h-12 w-12 rounded-full border border-black/10"
                        loading="lazy"
                        decoding="async" />
                @endif

                <div>
                    @if ($name !== '')
                        <div class="font-semibold">{{ $name }}</div>
                    @endif

                    @if ($role !== '')
                        <div class="text-sm text-black/60">{{ $role }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
