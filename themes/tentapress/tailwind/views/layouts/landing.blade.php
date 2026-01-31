<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @include('tentapress-seo::head', ['page' => $page])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-green-50 text-zinc-900">
        <main class="mx-auto max-w-5xl p-6">
            @include('tentapress-pages::partials.blocks', [
                'blocks' => $page->blocks,
            ])
        </main>
    </body>
</html>
