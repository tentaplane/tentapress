<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @include('tentapress-seo::head', ['page' => $page])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/bootstrap/build')
    </head>
    <body>
        <main class="container py-4">
            @include('tentapress-pages::partials.blocks', [
                'blocks' => $page->blocks,
            ])
        </main>
    </body>
</html>
