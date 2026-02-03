<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['post' => $post ?? null, 'page' => $page ?? null])

        @php($manifest = public_path('themes/tentapress/tailwind/build/manifest.json'))
        @if (is_file($manifest))
            @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
        @endif
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        @php
            $entry = $post ?? $page ?? null;
            $author = $post?->author;
            $publishedAt = $entry?->published_at ?? $entry?->created_at;
            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
        @endphp
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            <div class="pointer-events-none absolute -top-40 left-1/2 h-96 w-[54rem] -translate-x-1/2 rounded-full bg-brand-500/15 blur-[120px]"></div>
            <div class="pointer-events-none absolute left-0 top-40 h-72 w-72 rounded-full bg-indigo-400/10 blur-[100px]"></div>
            <div class="pointer-events-none absolute right-0 top-10 h-64 w-64 rounded-full bg-sky-400/10 blur-[90px]"></div>

            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-4xl flex-1 px-6 pb-20 pt-12">
                <header class="space-y-3 pb-10">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">Blog</div>
                    <h1 class="font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        {{ $entry?->title ?? 'Post' }}
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

                @if ($post)
                    @include('tentapress-posts::partials.blocks', [
                        'blocks' => $post->blocks,
                    ])
                @elseif ($page)
                    @include('tentapress-pages::partials.blocks', [
                        'blocks' => $page->blocks,
                    ])
                @endif
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
