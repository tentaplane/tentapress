@php
    $variant = isset($variant) ? (string) $variant : '';
    $eyebrow = (string) ($props['eyebrow'] ?? '');
    $headline = (string) ($props['headline'] ?? '');
    $sub = (string) ($props['subheadline'] ?? '');
    $bg = (string) ($props['background_image'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $imagePosition = (string) ($props['image_position'] ?? 'top');
    $rawActions = $props['actions'] ?? [];
	
    if (is_string($rawActions)) {
        $trim = trim($rawActions);
        $decoded = $trim !== '' ? json_decode($trim, true) : null;
		
        if (is_array($decoded)) {
            $actions = $decoded;
        } else {
            $lines = preg_split('/\r?\n/', $trim) ?: [];
            $actions = [];
			
            foreach ($lines as $line) {
                $line = trim($line);
				
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $actions[] = [
                    'label' => $parts[0] ?? $line,
                    'url' => $parts[1] ?? '',
                    'style' => $parts[2] ?? 'primary',
                ];
            }
        }
    } elseif (is_array($rawActions)) {
        $actions = $rawActions;
    } else {
        $actions = [];
    }

    $actions = array_values(array_filter($actions, static fn ($item) => is_array($item) && ($item['label'] ?? '') !== ''));

    $primary = $actions[0] ?? [];
    $secondary = $actions[1] ?? [];

    $ctaLabel = (string) ($primary['label'] ?? '');
    $ctaUrl = (string) ($primary['url'] ?? '');
    $ctaStyle = (string) ($primary['style'] ?? 'primary');
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $splitLayout = $variant === 'split';
    $hasBackground = $bg !== '' && ! $splitLayout;

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';

    $backgroundRef = null;
    if ($bg !== '') {
        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $backgroundRef = $resolver->resolveImage(
                ['url' => $bg, 'alt' => ''],
                ['variant' => 'large', 'sizes' => '(min-width: 1280px) 1120px, 100vw']
            );
        }
    }

    $backgroundSrc = is_array($backgroundRef) ? (string) ($backgroundRef['src'] ?? '') : $bg;
    $backgroundSrcset = is_array($backgroundRef) ? ($backgroundRef['srcset'] ?? null) : null;
    $backgroundSizes = is_array($backgroundRef) ? ($backgroundRef['sizes'] ?? null) : null;
    $backgroundWidth = is_array($backgroundRef) && isset($backgroundRef['width']) && is_int($backgroundRef['width']) ? $backgroundRef['width'] : null;
    $backgroundHeight = is_array($backgroundRef) && isset($backgroundRef['height']) && is_int($backgroundRef['height']) ? $backgroundRef['height'] : null;
    $backgroundLoading = 'eager';
    $backgroundFetchPriority = 'high';
@endphp

@if ($splitLayout)
    {{-- Split variant: two-column text + image --}}
    <section class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen overflow-hidden py-20 sm:py-28">
        <div class="relative mx-auto max-w-7xl px-6">
            <div class="grid gap-14 lg:grid-cols-2 lg:items-center {{ $alignClass }}">
                <div class="space-y-6">
                    @if ($eyebrow !== '')
                        <div class="text-xs font-semibold uppercase tracking-[0.25em] text-surface-500">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if ($headline !== '')
                        <h1 class="text-balance font-display text-5xl font-semibold tracking-tight text-surface-900 sm:text-6xl lg:text-7xl">
                            {{ $headline }}
                        </h1>
                    @endif

                    @if ($sub !== '')
                        <p class="text-pretty text-lg leading-relaxed text-surface-600 sm:text-xl">{{ $sub }}</p>
                    @endif

                    @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                        <div class="flex flex-wrap gap-4 pt-2 {{ $actionsClass }}">
                            @if ($ctaLabel !== '' && $ctaUrl !== '')
                                @php
                                    $ctaClass = match ($ctaStyle) {
                                        'outline' => 'border border-black/[0.08] text-surface-700 hover:bg-surface-50',
                                        'ghost' => 'text-surface-600 hover:text-surface-900',
                                        default => 'bg-surface-900 text-white hover:opacity-80',
                                    };
                                @endphp
                                <a
                                    href="{{ $ctaUrl }}"
                                    class="inline-flex items-center rounded-lg px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2 {{ $ctaClass }}">
                                    {{ $ctaLabel }}
                                </a>
                            @endif

                            @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                                <a
                                    href="{{ $secondaryUrl }}"
                                    class="inline-flex items-center rounded-lg border border-black/8 px-7 py-3.5 text-sm font-semibold text-surface-700 transition-all hover:bg-surface-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2">
                                    {{ $secondaryLabel }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($backgroundSrc !== '')
                    <div class="overflow-hidden rounded-[2.5rem] border border-black/8 bg-surface-100">
                        <img
                            src="{{ $backgroundSrc }}"
                            alt=""
                            @if (is_string($backgroundSrcset) && $backgroundSrcset !== '') srcset="{{ $backgroundSrcset }}" @endif
                            @if (is_string($backgroundSizes) && $backgroundSizes !== '') sizes="{{ $backgroundSizes }}" @endif
                            @if (is_int($backgroundWidth) && $backgroundWidth > 0) width="{{ $backgroundWidth }}" @endif
                            @if (is_int($backgroundHeight) && $backgroundHeight > 0) height="{{ $backgroundHeight }}" @endif
                            class="h-full w-full object-cover"
                            loading="{{ $backgroundLoading }}"
                            fetchpriority="{{ $backgroundFetchPriority }}"
                            decoding="async" />
                    </div>
                @endif
            </div>
        </div>
    </section>
@else
    {{-- Default variant: full-bleed background image with overlaid text --}}
    <section class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen overflow-hidden {{ $hasBackground ? 'min-h-[70vh] flex items-center py-24 sm:py-32' : 'py-20 sm:py-28' }}">
        @if ($hasBackground)
            <div class="absolute inset-0">
                <img
                    src="{{ $backgroundSrc }}"
                    alt=""
                    @if (is_string($backgroundSrcset) && $backgroundSrcset !== '') srcset="{{ $backgroundSrcset }}" @endif
                    @if (is_string($backgroundSizes) && $backgroundSizes !== '') sizes="{{ $backgroundSizes }}" @endif
                    @if (is_int($backgroundWidth) && $backgroundWidth > 0) width="{{ $backgroundWidth }}" @endif
                    @if (is_int($backgroundHeight) && $backgroundHeight > 0) height="{{ $backgroundHeight }}" @endif
                    class="h-full w-full object-cover"
                    loading="{{ $backgroundLoading }}"
                    fetchpriority="{{ $backgroundFetchPriority }}"
                    decoding="async" />
                <div class="absolute inset-0 bg-linear-to-br from-surface-950/85 via-surface-950/60 to-surface-900/40"></div>
            </div>
        @endif

        <div class="relative mx-auto w-full max-w-7xl px-6">
            <div class="flex flex-col space-y-6 {{ $alignClass }}">
                <div class="space-y-6 max-w-3xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">
                    @if ($eyebrow !== '')
                        <div class="text-xs font-semibold uppercase tracking-[0.25em] {{ $hasBackground ? 'text-white/70' : 'text-surface-500' }}">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if ($headline !== '')
                        <h1 class="text-balance font-display text-5xl font-semibold tracking-tight sm:text-6xl lg:text-7xl {{ $hasBackground ? 'text-white' : 'text-surface-900' }}">
                            {{ $headline }}
                        </h1>
                    @endif

                    @if ($sub !== '')
                        <p class="text-pretty text-lg leading-relaxed sm:text-xl {{ $hasBackground ? 'text-white/85' : 'text-surface-600' }}">{{ $sub }}</p>
                    @endif

                    @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                        <div class="flex flex-wrap gap-4 pt-2 {{ $actionsClass }}">
                            @if ($ctaLabel !== '' && $ctaUrl !== '')
                                @php
                                    $ctaClass = match ($ctaStyle) {
                                        'outline' => $hasBackground ? 'border border-white/40 text-white hover:bg-white/10' : 'border border-black/[0.08] text-surface-700 hover:bg-surface-50',
                                        'ghost' => $hasBackground ? 'text-white/70 hover:text-white' : 'text-surface-600 hover:text-surface-900',
                                        default => $hasBackground ? 'bg-white text-surface-900 hover:bg-white/90' : 'bg-surface-900 text-white hover:opacity-80',
                                    };
                                @endphp
                                <a
                                    href="{{ $ctaUrl }}"
                                    class="inline-flex items-center rounded-lg px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 {{ $ctaClass }}">
                                    {{ $ctaLabel }}
                                </a>
                            @endif

                            @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                                <a
                                    href="{{ $secondaryUrl }}"
                                    class="inline-flex items-center rounded-lg border px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:ring-offset-2 {{ $hasBackground ? 'border-white/40 text-white/90 hover:bg-white/10 hover:text-white' : 'border-black/[0.08] text-surface-700 hover:bg-surface-50' }}">
                                    {{ $secondaryLabel }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
