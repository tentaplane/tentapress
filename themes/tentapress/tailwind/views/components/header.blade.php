@props([
    'title' => 'TentaPress',
    'tagline' => 'Launch client sites faster',
    'ctaLabel' => 'Get started',
    'ctaUrl' => '/admin',
])

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-5">
        <a class="flex items-center gap-3" href="/">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 via-sky-500 to-indigo-500 text-sm font-bold text-white shadow-lg shadow-brand-600/30">
                TP
            </span>
            <span class="leading-tight">
                <span class="block font-display text-lg font-semibold tracking-tight text-slate-900">{{ $title }}</span>
                <span class="block text-xs text-slate-500">{{ $tagline }}</span>
            </span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
            <x-tp-theme::menu location="primary" class="text-sm font-medium text-slate-600">
                <a class="hover:text-slate-900" href="/">Home</a>
            </x-tp-theme::menu>
            <a
                class="inline-flex items-center rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30"
                href="{{ $ctaUrl }}">
                {{ $ctaLabel }}
            </a>
        </div>

        <a class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 lg:hidden" href="{{ $ctaUrl }}">
            {{ $ctaLabel }}
        </a>
    </div>
</header>
