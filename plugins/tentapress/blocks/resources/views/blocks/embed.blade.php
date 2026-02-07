@php
    $title = (string) ($props['title'] ?? '');
    $url = trim((string) ($props['url'] ?? ''));
    $aspect = (string) ($props['aspect'] ?? '16:9');
    $height = (int) ($props['height'] ?? 480);
    $allowFullscreen = filter_var($props['allow_fullscreen'] ?? true, FILTER_VALIDATE_BOOL);
    $caption = (string) ($props['caption'] ?? '');

    $youtubePrivacyMode = filter_var($props['youtube_privacy_mode'] ?? true, FILTER_VALIDATE_BOOL);
    $youtubeControls = filter_var($props['youtube_controls'] ?? true, FILTER_VALIDATE_BOOL);
    $youtubeFs = filter_var($props['youtube_fs'] ?? true, FILTER_VALIDATE_BOOL);
    $youtubeDisableKb = filter_var($props['youtube_disablekb'] ?? false, FILTER_VALIDATE_BOOL);
    $youtubeCaptions = filter_var($props['youtube_captions'] ?? false, FILTER_VALIDATE_BOOL);
    $youtubeCaptionLang = trim((string) ($props['youtube_caption_lang'] ?? ''));
    $youtubePlaysInline = filter_var($props['youtube_playsinline'] ?? true, FILTER_VALIDATE_BOOL);
    $youtubeAutoplay = filter_var($props['youtube_autoplay'] ?? false, FILTER_VALIDATE_BOOL);
    $youtubeLoop = filter_var($props['youtube_loop'] ?? false, FILTER_VALIDATE_BOOL);
    $youtubeRelated = (string) ($props['youtube_related'] ?? 'same_channel');
    $youtubeStart = trim((string) ($props['youtube_start'] ?? ''));
    $youtubeEnd = trim((string) ($props['youtube_end'] ?? ''));

    $aspectClass = match ($aspect) {
        'square' => 'aspect-square',
        '4:3' => 'aspect-[4/3]',
        '16:9' => 'aspect-video',
        default => '',
    };

    $embedUrl = $url;
    $videoId = '';

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

            $baseHost = $youtubePrivacyMode ? 'www.youtube-nocookie.com' : 'www.youtube.com';
            $basePath = $videoId !== '' ? '/embed/'.$videoId : ('/'.trim($path, '/'));
            $base = 'https://'.$baseHost.$basePath;

            $ytParams = $query;
            $ytParams['controls'] = $youtubeControls ? '1' : '0';
            $ytParams['fs'] = $youtubeFs ? '1' : '0';
            $ytParams['disablekb'] = $youtubeDisableKb ? '1' : '0';
            $ytParams['cc_load_policy'] = $youtubeCaptions ? '1' : '0';
            $ytParams['playsinline'] = $youtubePlaysInline ? '1' : '0';
            $ytParams['autoplay'] = $youtubeAutoplay ? '1' : '0';
            $ytParams['rel'] = $youtubeRelated === 'all' ? '1' : '0';

            if ($youtubeCaptionLang !== '') {
                $ytParams['cc_lang_pref'] = $youtubeCaptionLang;
            } else {
                unset($ytParams['cc_lang_pref']);
            }

            if (is_numeric($youtubeStart) && (int) $youtubeStart >= 0) {
                $ytParams['start'] = (string) ((int) $youtubeStart);
            } else {
                unset($ytParams['start']);
            }

            if (is_numeric($youtubeEnd) && (int) $youtubeEnd >= 0) {
                $ytParams['end'] = (string) ((int) $youtubeEnd);
            } else {
                unset($ytParams['end']);
            }

            if ($youtubeLoop) {
                $ytParams['loop'] = '1';
                if ($videoId !== '') {
                    $ytParams['playlist'] = $videoId;
                }
            } else {
                unset($ytParams['loop'], $ytParams['playlist']);
            }

            $embedUrl = $base;
            if ($ytParams !== []) {
                $embedUrl .= '?'.http_build_query($ytParams);
            }
        }
    }
@endphp

@if ($embedUrl !== '')
    <section class="py-10">
        <div class="mx-auto max-w-5xl space-y-3 px-6">
            @if ($title !== '')
                <h2 class="text-xl font-semibold">{{ $title }}</h2>
            @endif
            <div class="overflow-hidden rounded-xl border border-black/10 bg-white">
                <div class="w-full {{ $aspectClass }}" @if ($aspect === 'auto') style="height: {{ $height }}px" @endif>
                    <iframe
                        src="{{ $embedUrl }}"
                        class="h-full w-full"
                        loading="lazy"
                        @if ($allowFullscreen) allowfullscreen @endif></iframe>
                </div>
            </div>
            @if ($caption !== '')
                <div class="text-sm text-black/60">{{ $caption }}</div>
            @endif
        </div>
    </section>
@endif
