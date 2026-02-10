@php
    $image = trim((string) ($props['image'] ?? ''));
    $imagePosition = trim((string) ($props['image_position'] ?? 'left'));
    $eyebrow = trim((string) ($props['eyebrow'] ?? ''));
    $heading = trim((string) ($props['heading'] ?? ''));
    $subheading = trim((string) ($props['subheading'] ?? ''));
    $body = trim((string) ($props['body'] ?? ''));

    $mediaOrder = $imagePosition === 'right' ? 'lg:order-2' : 'lg:order-1';
    $contentOrder = $imagePosition === 'right' ? 'lg:order-1' : 'lg:order-2';

    $imageRef = null;
    if ($image !== '') {
        $resolver = app()->bound('tp.media.reference_resolver') ? app('tp.media.reference_resolver') : null;
        if (is_object($resolver) && method_exists($resolver, 'resolveImage')) {
            $imageRef = $resolver->resolveImage(
                ['url' => $image, 'alt' => ''],
                ['variant' => 'large', 'sizes' => '(min-width: 1024px) 560px, 100vw']
            );
        }
    }

    $imageSrc = is_array($imageRef) ? (string) ($imageRef['src'] ?? '') : $image;
    $imageSrcset = is_array($imageRef) ? ($imageRef['srcset'] ?? null) : null;
    $imageSizes = is_array($imageRef) ? ($imageRef['sizes'] ?? null) : null;
@endphp

<section>
    <div class="bg-surface-50">
        <div class="mx-auto max-w-6xl px-6 py-12 sm:py-16">
            <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-6 {{ $mediaOrder }}">
                    @if ($imageSrc !== '')
                        <div class="overflow-hidden rounded-[2.5rem] border border-black/10 bg-white shadow-sm">
                            <img
                                src="{{ $imageSrc }}"
                                alt=""
                                @if (is_string($imageSrcset) && $imageSrcset !== '') srcset="{{ $imageSrcset }}" @endif
                                @if (is_string($imageSizes) && $imageSizes !== '') sizes="{{ $imageSizes }}" @endif
                                class="h-full w-full object-cover"
                                loading="lazy"
                                decoding="async" />
                        </div>
                    @else
                        <div class="flex min-h-72 items-center justify-center rounded-[2.5rem] border border-dashed border-black/15 bg-white/70 text-sm text-surface-500">
                            Upload an image to complete the block.
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-6 {{ $contentOrder }}">
                    <div class="flex flex-col gap-4">
                        @if ($eyebrow !== '')
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-surface-500">{{ $eyebrow }}</p>
                        @endif

                        @if ($heading !== '')
                            <h2 class="text-balance font-display text-3xl font-semibold text-surface-900 sm:text-4xl">
                                {{ $heading }}
                            </h2>
                        @endif

                        @if ($subheading !== '')
                            <p class="text-pretty text-lg font-medium text-surface-700">
                                {{ $subheading }}
                            </p>
                        @endif

                        @if ($body !== '')
                            <p class="text-pretty text-base leading-relaxed text-surface-600 sm:text-lg">
                                {!! nl2br($body) !!}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
