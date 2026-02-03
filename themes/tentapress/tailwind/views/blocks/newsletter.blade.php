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

<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-6">
        <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white p-10 shadow-lg shadow-slate-200/60 sm:p-12">
            <div class="pointer-events-none absolute -left-16 top-0 h-36 w-36 rounded-full bg-brand-100/80 blur-[100px]"></div>
            <div class="pointer-events-none absolute -right-12 top-6 h-40 w-40 rounded-full bg-indigo-200/50 blur-[120px]"></div>

            <div class="relative flex flex-col gap-4 {{ $alignClass }}">
                @if ($title !== '')
                    <h2 class="text-balance font-display text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                        {{ $title }}
                    </h2>
                @endif
                @if ($body !== '')
                    <p class="text-pretty text-lg text-slate-500">{{ $body }}</p>
                @endif

                <form action="{{ $action !== '' ? $action : '#' }}" method="post" class="flex flex-wrap gap-3 {{ $formClass }}">
                    <input
                        type="email"
                        name="email"
                        class="min-w-[240px] flex-1 rounded-full border border-slate-200 px-5 py-3 text-sm text-slate-700 placeholder:text-slate-400"
                        placeholder="{{ $placeholder }}" />
                    <button type="submit" class="rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/30">
                        {{ $buttonLabel }}
                    </button>
                </form>

                @if ($disclaimer !== '')
                    <div class="text-xs text-slate-400">{{ $disclaimer }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
