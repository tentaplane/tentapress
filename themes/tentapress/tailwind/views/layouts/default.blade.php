<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['page' => $page])

        @php($manifest = public_path('themes/tentapress/tailwind/build/manifest.json'))
        @if (is_file($manifest))
            @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
        @endif
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute -top-32 left-1/2 h-96 w-[48rem] -translate-x-1/2 rounded-full bg-blue-500/10 blur-[120px]"></div>
            <div class="pointer-events-none absolute right-0 top-24 h-72 w-72 rounded-full bg-indigo-500/10 blur-[100px]"></div>

            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                    <a href="/" class="flex items-center gap-2 text-base font-semibold tracking-tight text-slate-900">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white">TP</span>
                        TentaPress
                    </a>
                    <div class="hidden items-center gap-6 md:flex">
                        <x-tp-theme::menu location="primary" class="text-sm text-slate-600">
                            <a href="/" class="hover:text-slate-900">Home</a>
                        </x-tp-theme::menu>
                        <a
                            href="/admin"
                            class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800">
                            Start free
                        </a>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-6 pb-20 pt-6">
                @include('tentapress-pages::partials.blocks', [
                    'blocks' => $page->blocks,
                ])
            </main>

            <footer class="border-t border-slate-200/70 bg-white/70">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-8 text-sm text-slate-500">
                    <div>&copy; {{ date('Y') }} TentaPress. All rights reserved.</div>
                    <div class="flex gap-4">
                        <a href="/admin" class="hover:text-slate-900">Admin</a>
                        <a href="#" class="hover:text-slate-900">Privacy</a>
                        <a href="#" class="hover:text-slate-900">Terms</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
