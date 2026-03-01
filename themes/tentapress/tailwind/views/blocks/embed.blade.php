@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['url'] ?? ''));
    $aspect = (string) ($props['aspect'] ?? '16:9');
    $height = (int) ($props['height'] ?? 480);
    $allowFullscreen = filter_var($props['allow_fullscreen'] ?? true, FILTER_VALIDATE_BOOL);
    $caption = (string) ($props['caption'] ?? '');
    $isBuilderPreview = (bool) ($isBuilderPreview ?? false);

    $aspectClass = match ($aspect) {
        'square' => 'aspect-square',
        '4:3' => 'aspect-[4/3]',
        '16:9' => 'aspect-video',
        default => '',
    };

    $host = '';
    $videoId = '';
    $thumbnailUrl = '';

    if ($url !== '') {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        $isYouTubeHost = in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true);

        if ($isYouTubeHost) {
            if (($host === 'youtu.be' || str_starts_with($path, 'watch')) && isset($query['v'])) {
                $videoId = trim((string) $query['v']);
            } elseif ($host === 'youtu.be') {
                $videoId = trim((string) $path);
            } elseif (str_starts_with($path, 'embed/')) {
                $videoId = trim((string) substr($path, 6));
            }

            if ($videoId !== '') {
                $thumbnailUrl = 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';
            }
        }
    }

    $hostLabel = $host !== '' ? preg_replace('/^www\./', '', $host) : 'external media';
@endphp

@if ($url !== '')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-5 px-6">
            @if ($title !== '')
                <h2 class="font-display text-3xl font-semibold text-surface-900 sm:text-4xl">
                    {{ $title }}
                </h2>
            @endif
            <div class="overflow-hidden rounded-[2.5rem] border border-black/[0.08] bg-white">
                <div class="w-full {{ $aspectClass }}" @if ($aspect === 'auto') style="height: {{ $height }}px" @endif>
                    @if ($isBuilderPreview)
                        <div class="relative flex h-full w-full items-center justify-center bg-slate-950 text-white">
                            @if ($thumbnailUrl !== '')
                                <img src="{{ $thumbnailUrl }}" alt="Video thumbnail" class="h-full w-full object-cover" loading="lazy" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-black/20"></div>
                                <div class="absolute inset-x-8 bottom-8 flex items-end justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-xs uppercase tracking-[0.24em] text-white/70">Video preview</div>
                                        <div class="truncate text-sm font-medium text-white/90">{{ $hostLabel }}</div>
                                    </div>
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-slate-950 shadow-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-7 w-7 fill-current">
                                            <path d="M8.5 6.5v11l8-5.5-8-5.5Z" />
                                        </svg>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-full w-full flex-col items-center justify-center gap-4 bg-slate-100 px-8 text-center text-slate-700">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 8.25 4.5 3.75-4.5 3.75v-7.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-display text-xl font-semibold text-surface-900">Embed preview disabled in builder</div>
                                        <div class="mt-1 text-sm text-surface-500">{{ $hostLabel }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <iframe
                            src="{{ $url }}"
                            class="h-full w-full"
                            loading="lazy"
                            @if ($allowFullscreen) allowfullscreen @endif></iframe>
                    @endif
                </div>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-surface-500">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
