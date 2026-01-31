<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $post->title }}</title>

        @vite(['resources/css/fallback.css'])
    </head>

    <body class="bg-white text-slate-900">
        @php
            $author = $post->author;
            $publishedAt = $post->published_at ?? $post->created_at;
            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
        @endphp

        <main class="mx-auto max-w-3xl space-y-10 px-6 py-12">
            <article class="space-y-8">
                <header class="space-y-3">
                    <div class="text-xs font-semibold tracking-[0.2em] text-slate-400 uppercase">Blog</div>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                        {{ $post->title }}
                    </h1>
                    <div class="flex flex-wrap gap-x-4 text-sm text-slate-500">
                        @if ($publishedLabel !== '')
                            <span>{{ $publishedLabel }}</span>
                        @endif

                        @if ($author)
                            <span>By {{ $author->name ?: 'Author #' . $author->id }}</span>
                        @endif
                    </div>
                </header>

                <div class="space-y-6">
                    {!! $blocksHtml !!}
                </div>
            </article>
        </main>
    </body>
</html>
