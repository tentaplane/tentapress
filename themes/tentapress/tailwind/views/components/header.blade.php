@props([
    'title' => 'TentaPress',
    'tagline' => 'Launch client sites faster',
    'ctaLabel' => 'Get started',
    'ctaUrl' => '/admin',
])

<header class="sticky top-0 z-40 border-b border-surface-200/80 bg-white/90 backdrop-blur-sm">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
        <a class="group flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2" href="/">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white shadow-brand transition-shadow group-hover:shadow-xl">
                TP
            </span>
            <span class="leading-tight">
                <span class="block font-display text-lg font-semibold tracking-tight text-surface-900">{{ $title }}</span>
                <span class="block text-xs text-surface-500">{{ $tagline }}</span>
            </span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
            <x-tp-theme::menu location="primary" class="text-sm font-medium text-surface-600">
                <a class="rounded-md px-1 py-0.5 transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" href="/">Home</a>
            </x-tp-theme::menu>
            <a
                class="inline-flex items-center rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-brand transition-all hover:bg-brand-700 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                href="{{ $ctaUrl }}">
                {{ $ctaLabel }}
            </a>
        </div>

        <a class="inline-flex items-center rounded-full border border-surface-200 bg-white px-4 py-2 text-xs font-semibold text-surface-700 transition-colors hover:border-surface-300 hover:bg-surface-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 lg:hidden" href="{{ $ctaUrl }}">
            {{ $ctaLabel }}
        </a>
    </div>
</header>
