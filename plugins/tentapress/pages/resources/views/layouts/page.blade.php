<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $page->title }}</title>
    </head>

    <body class="bg-white text-slate-900">
        <main class="mx-auto max-w-4xl space-y-8 px-6 py-12">
            <header class="space-y-2">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                    {{ $page->title }}
                </h1>
            </header>

            <article class="prose prose-slate max-w-none">
                {!! $blocksHtml !!}
            </article>
        </main>
    </body>
</html>
