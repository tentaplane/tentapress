@props([
    'title' => 'TentaPress',
    'tagline' => 'Launch client sites faster',
    'ctaLabel' => 'Get started',
    'ctaUrl' => '/admin',
])

<header class="sticky top-0 z-40 border-b border-black/[0.08] bg-page/90 backdrop-blur-sm">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
        <a class="group flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2" href="/">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-surface-900 text-sm font-bold text-white transition-opacity group-hover:opacity-80">
                TP
            </span>
            <span class="leading-tight">
                <span class="block font-display text-lg font-semibold tracking-tight text-surface-900">{{ $title }}</span>
                <span class="block text-xs text-surface-500">{{ $tagline }}</span>
            </span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
            <x-tp-theme::menu location="primary" class="text-sm font-medium text-surface-600">
                <a class="rounded-md px-1 py-0.5 transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="/">Home</a>
            </x-tp-theme::menu>
            <a
                class="inline-flex items-center rounded-lg bg-surface-900 px-5 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2"
                href="{{ $ctaUrl }}">
                {{ $ctaLabel }}
            </a>
        </div>

        <a class="inline-flex items-center rounded-lg border border-black/[0.08] bg-white px-4 py-2 text-xs font-semibold text-surface-700 transition-colors hover:bg-surface-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900 focus-visible:ring-offset-2 lg:hidden" href="{{ $ctaUrl }}">
            {{ $ctaLabel }}
        </a>
    </div>
</header>
