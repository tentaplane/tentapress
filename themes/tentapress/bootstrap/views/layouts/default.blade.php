<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @include('tentapress-seo::head', ['page' => $page])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/bootstrap/build')
    </head>
    <body>
        <header class="border-bottom">
            <div class="d-flex align-items-center justify-content-between container flex-wrap gap-3 py-4">
                <div class="fw-semibold">TentaPress</div>
                <x-tp-theme::menu location="primary" />
            </div>
        </header>

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
