<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['post' => $post ?? null, 'page' => $page ?? null])
        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-page font-sans text-surface-900 antialiased">
        @php
            $entry = $post ?? $page ?? null;
            $author = $post?->author;
            $publishedAt = $entry?->published_at ?? $entry?->created_at;
            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
        @endphp
        <div class="relative flex min-h-screen flex-col">
            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-4xl flex-1 px-6">
                <header class="space-y-4 pb-12">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-surface-500">Blog</div>
                    <h1 class="font-display text-4xl font-bold text-surface-900 sm:text-5xl">
                        {{ $entry?->title ?? 'Post' }}
                    </h1>
                    <div class="flex flex-wrap gap-x-4 text-sm text-surface-500">
                        @if ($publishedLabel !== '')
                            <span>{{ $publishedLabel }}</span>
                        @endif

                        @if ($author)
                            <span>By {{ $author->name ?: 'Author #' . $author->id }}</span>
                        @endif
                    </div>
                </header>

                <article class="tp-page-content {{ ! empty($isPageEditorContent) ? 'tp-page-content--page' : '' }}">
                    {!! $blocksHtml !!}
                </article>
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
