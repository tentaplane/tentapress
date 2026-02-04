@php
    $title = (string) ($props['title'] ?? '');
    $body = (string) ($props['body'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');
    $background = (string) ($props['background'] ?? 'white');
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

    $btnLabel = (string) ($primary['label'] ?? '');
    $btnUrl = (string) ($primary['url'] ?? '');
    $btnStyle = (string) ($primary['style'] ?? 'primary');
    $secondaryLabel = (string) ($secondary['label'] ?? '');
    $secondaryUrl = (string) ($secondary['url'] ?? '');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $actionsClass = $alignment === 'center' ? 'justify-center' : 'justify-start';

    $panelClass = match ($background) {
        'none' => 'bg-transparent border-transparent',
        'muted' => 'bg-surface-100 border-black/[0.08]',
        default => 'bg-white border-black/[0.08]',
    };

    $btnClass = match ($btnStyle) {
        'outline' => 'border border-black/[0.08] text-surface-700 hover:bg-surface-50',
        'ghost' => 'text-surface-600 hover:text-surface-900',
        default => 'bg-surface-900 text-white hover:opacity-80',
    };
@endphp

<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-5xl px-6">
        <div class="overflow-hidden rounded-[2.5rem] border {{ $panelClass }} p-10 sm:p-16 bg-white">
            <div class="flex flex-col gap-5 {{ $alignClass }}">
                @if ($title !== '')
                    <h2 class="text-balance font-display text-4xl font-semibold text-surface-900 sm:text-5xl">
                        {{ $title }}
                    </h2>
                @endif

                @if ($body !== '')
                    <p class="max-w-2xl text-pretty whitespace-pre-wrap text-lg leading-relaxed text-surface-600">{{ $body }}</p>
                @endif

                @if (($btnLabel !== '' && $btnUrl !== '') || ($secondaryLabel !== '' && $secondaryUrl !== ''))
                    <div class="mt-2 flex flex-wrap gap-4 {{ $actionsClass }}">
                        @if ($btnLabel !== '' && $btnUrl !== '')
                            <a
                                href="{{ $btnUrl }}"
                                class="inline-flex items-center rounded-lg px-7 py-3.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2 {{ $btnClass }}">
                                {{ $btnLabel }}
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
        </div>
    </div>
</section>
