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
    <body class="bg-surface-50 font-sans text-surface-900 antialiased">
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            <div class="pointer-events-none absolute -top-48 left-1/2 h-[30rem] w-[64rem] -translate-x-1/2 rounded-full bg-brand-300/25 blur-[160px]"></div>
            <div class="pointer-events-none absolute left-0 top-40 h-72 w-72 rounded-full bg-brand-200/20 blur-[120px]"></div>
            <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-accent-200/25 blur-[100px]"></div>

            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-7xl flex-1 px-6 pb-24 pt-10">
                @include('tentapress-pages::partials.blocks', [
                    'blocks' => $page->blocks,
                ])
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
