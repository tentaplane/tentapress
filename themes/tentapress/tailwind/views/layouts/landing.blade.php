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
    <body class="bg-green-50 text-zinc-900">
        <main class="mx-auto max-w-5xl p-6">
            @include('tentapress-pages::partials.blocks', [
            'blocks' => $page->blocks,
            ])
        </main>
    </body>

    <footer class="border-t border-black/10">
        <div class="mx-auto max-w-5xl p-6 text-sm text-black/60">&copy; {{ date('Y') }} Your Company</div>
    </footer>
</html>
