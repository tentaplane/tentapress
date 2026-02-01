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

    $alignClass = $alignment === 'center' ? 'text-center' : 'text-left';
    $formClass = $alignment === 'center' ? 'justify-center' : 'justify-start';
@endphp

<section class="py-12">
    <div class="mx-auto max-w-4xl space-y-4 rounded-xl border border-black/10 bg-white p-8 {{ $alignClass }}">
        @if ($title !== '')
            <h2 class="text-2xl font-semibold">{{ $title }}</h2>
        @endif
        @if ($body !== '')
            <p class="text-black/70">{{ $body }}</p>
        @endif

        <form action="{{ $action !== '' ? $action : '#' }}" method="post" class="flex flex-wrap gap-3 {{ $formClass }}">
            <input
                type="email"
                name="email"
                class="min-w-[220px] flex-1 rounded border border-black/10 px-4 py-2 text-sm"
                placeholder="{{ $placeholder }}" />
            <button type="submit" class="rounded bg-black px-4 py-2 text-sm font-semibold text-white">
                {{ $buttonLabel }}
            </button>
        </form>

        @if ($disclaimer !== '')
            <div class="text-xs text-black/60">{{ $disclaimer }}</div>
        @endif
    </div>
</section>
