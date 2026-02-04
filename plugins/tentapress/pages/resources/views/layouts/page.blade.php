<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Fallback - {{ $page->title }}</title>
    </head>

    <body class="bg-white text-green-400">
        <main class="mx-auto max-w-4xl p-6">
            @include('tentapress-pages::partials.blocks', [
            'blocks' => $page->blocks,
            ])
        </main>
    </body>
</html>
