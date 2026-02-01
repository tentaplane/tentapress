<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['post' => $post ?? null, 'page' => $page ?? null])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-green-50 text-zinc-900">
        @php
            $entry = $post ?? $page ?? null;
            $author = $post?->author;
            $publishedAt = $entry?->published_at ?? $entry?->created_at;
            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
        @endphp

        <header class="border-b border-black/10">
            <div class="mx-auto flex max-w-5xl items-center justify-between p-6">
                <div class="font-semibold">Tailwind Theme - TentaPress</div>
                <x-tp-theme::menu location="primary">
                    <a href="/" class="hover:underline">Home</a>
                </x-tp-theme::menu>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-8 p-6">
            <header class="space-y-2">
                <div class="text-xs font-semibold tracking-[0.2em] text-black/50 uppercase">Blog</div>
                <h1 class="text-3xl font-semibold tracking-tight">
                    {{ $entry?->title ?? 'Post' }}
                </h1>
                <div class="flex flex-wrap gap-x-4 text-sm text-black/60">
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

        <footer class="border-t border-black/10">
            <div class="mx-auto max-w-5xl p-6 text-sm text-black/60">&copy; {{ date('Y') }} Your Company</div>
        </footer>
    </body>
</html>
