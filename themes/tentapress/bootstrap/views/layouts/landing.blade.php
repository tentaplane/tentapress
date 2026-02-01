<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @include('tentapress-seo::head', ['page' => $page])

        @php($manifest = public_path('themes/tentapress/bootstrap/build/manifest.json'))
        @if (is_file($manifest))
            @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/bootstrap/build')
        @endif
    </head>
    <body>
        <main class="container py-4">
            @include('tentapress-pages::partials.blocks', [
            'blocks' => $page->blocks,
            ])
        </main>

        <footer class="border-top">
            <div class="small text-secondary container py-4">&copy; {{ date('Y') }} Your Company</div>
        </footer>
    </body>
</html>
