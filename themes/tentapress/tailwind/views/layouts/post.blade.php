<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['post' => $post ?? null, 'page' => $page ?? null])

        @php
            $manifest = public_path('themes/tentapress/tailwind/build/manifest.json');
            $hotFile = public_path('themes/tentapress/tailwind/hot');
        @endphp
        @if (is_file($manifest) || is_file($hotFile))
            {{
                \Illuminate\Support\Facades\Vite::useHotFile($hotFile)
                    ->useBuildDirectory('themes/tentapress/tailwind/build')
                    ->withEntryPoints(['resources/css/theme.css', 'resources/js/theme.js'])
            }}
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
            <div class="pointer-events-none absolute -top-40 left-1/2 h-[28rem] w-[60rem] -translate-x-1/2 rounded-full bg-brand-500/20 blur-[140px]"></div>
            <div class="pointer-events-none absolute left-0 top-32 h-80 w-80 rounded-full bg-indigo-400/15 blur-[120px]"></div>
            <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 rounded-full bg-sky-400/15 blur-[110px]"></div>

            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-5xl flex-1 px-6 pb-24 pt-14">
                <header class="space-y-3 pb-10">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">Blog</div>
                    <h1 class="font-display text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
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
