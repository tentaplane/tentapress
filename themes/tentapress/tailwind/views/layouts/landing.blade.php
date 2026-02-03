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
    <body class="bg-white font-sans text-surface-900 antialiased">
        <div class="relative flex min-h-screen flex-col">
            <x-tp-theme::header />

            <main class="relative z-10 flex-1">
                @include('tentapress-pages::partials.blocks', [
                    'blocks' => $page->blocks,
                ])
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
