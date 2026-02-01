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

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';
    $splitLayout = $variant === 'split' || $imagePosition === 'right';
    $layoutClass = $splitLayout ? 'grid gap-6 md:grid-cols-2 md:items-center' : 'space-y-6';

    $ctaClass = match ($ctaStyle) {
        'outline' => 'border border-black/20 text-black',
        'ghost' => 'text-black/70 hover:text-black',
        default => 'bg-black text-white',
    };
@endphp

<section class="py-16">
    <div class="mx-auto max-w-5xl px-6">
        <div class="rounded-xl border border-black/10 bg-white p-8">
            <div class="{{ $layoutClass }} {{ $alignClass }}">
                @if ($bg !== '' && ! $splitLayout)
                    <div class="overflow-hidden rounded-lg border border-black/10">
                        <img src="{{ $bg }}" alt="" class="h-auto w-full" />
                    </div>
                @endif

                <div class="space-y-4">
                    @if ($eyebrow !== '')
                        <div class="text-xs font-semibold tracking-[0.2em] text-black/50 uppercase">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if ($headline !== '')
                        <h1 class="text-3xl font-semibold">{{ $headline }}</h1>
                    @endif

                    @if ($sub !== '')
                        <p class="text-black/70">{{ $sub }}</p>
                    @endif

                    @if (($ctaLabel !== '' && $ctaUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                        <div class="mt-2 flex flex-wrap gap-3 {{ $actionsClass }}">
                            @if ($ctaLabel !== '' && $ctaUrl !== '')
                                <a
                                    href="{{ $ctaUrl }}"
                                    class="inline-flex items-center rounded px-4 py-2 text-sm font-semibold {{ $ctaClass }}">
                                    {{ $ctaLabel }}
                                </a>
                            @endif

                            @if ($secondaryLabel !== '' && $secondaryUrl !== '')
                                <a
                                    href="{{ $secondaryUrl }}"
                                    class="inline-flex items-center rounded border border-black/10 px-4 py-2 text-sm font-semibold text-black/70 hover:text-black">
                                    {{ $secondaryLabel }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($bg !== '' && $splitLayout)
                    <div class="overflow-hidden rounded-lg border border-black/10">
                        <img src="{{ $bg }}" alt="" class="h-auto w-full" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
