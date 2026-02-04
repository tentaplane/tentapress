@php
    $title = (string) ($props['title'] ?? '');
    $body = (string) ($props['body'] ?? '');
    $placeholder = (string) ($props['email_placeholder'] ?? 'you@example.com');
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
    $action = (string) ($primary['url'] ?? '');
    $buttonLabel = (string) ($primary['label'] ?? 'Subscribe');
    $disclaimer = (string) ($props['disclaimer'] ?? '');
    $alignment = (string) ($props['alignment'] ?? 'left');

    $alignClass = $alignment === 'center' ? 'text-center items-center' : 'text-left items-start';
    $formClass = $alignment === 'center' ? 'justify-center' : 'justify-start';
@endphp

<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-6">
        <div class="overflow-hidden rounded-[2.5rem] border border-black/[0.08] bg-white p-10 sm:p-14">
            <div class="flex flex-col gap-5 {{ $alignClass }}">
                @if ($title !== '')
                    <h2 class="text-balance font-display text-4xl font-semibold text-surface-900 sm:text-5xl">
                        {{ $title }}
                    </h2>
                @endif
                @if ($body !== '')
                    <p class="max-w-lg text-pretty text-lg leading-relaxed text-surface-600">{{ $body }}</p>
                @endif

                <form action="{{ $action !== '' ? $action : '#' }}" method="post" class="flex flex-wrap gap-3 {{ $formClass }}">
                    <input
                        type="email"
                        name="email"
                        class="min-w-[240px] flex-1 rounded-lg border border-black/[0.08] bg-surface-50 px-6 py-3.5 text-sm text-surface-800 placeholder:text-surface-400 focus:border-surface-400 focus:outline-none focus:ring-2 focus:ring-surface-900/10"
                        placeholder="{{ $placeholder }}" />
                    <button type="submit" class="rounded-lg bg-surface-900 px-7 py-3.5 text-sm font-semibold text-white transition-opacity hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2">
                        {{ $buttonLabel }}
                    </button>
                </form>

                @if ($disclaimer !== '')
                    <div class="text-xs text-surface-400">{{ $disclaimer }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
