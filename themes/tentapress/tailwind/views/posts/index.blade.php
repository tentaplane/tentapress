<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @php
            $seo = app(\TentaPress\Seo\Services\SeoManager::class)->forBlogIndex();
        @endphp

        @include('tentapress-seo::head', ['seo' => $seo])

        @php($manifest = public_path('themes/tentapress/tailwind/build/manifest.json'))
        @if (is_file($manifest))
            @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
        @endif
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="relative flex min-h-screen flex-col overflow-hidden">
            <div class="pointer-events-none absolute -top-40 left-1/2 h-[28rem] w-[60rem] -translate-x-1/2 rounded-full bg-brand-500/20 blur-[140px]"></div>
            <div class="pointer-events-none absolute left-0 top-32 h-80 w-80 rounded-full bg-indigo-400/15 blur-[120px]"></div>
            <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 rounded-full bg-sky-400/15 blur-[110px]"></div>

            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-6xl flex-1 space-y-12 px-6 pb-24 pt-14">
                <div class="space-y-3">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">Blog</div>
                    <h1 class="font-display text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Latest posts</h1>
                    <p class="text-base text-slate-500">Agency updates, product notes, and helpful guides.</p>
                </div>

                @if ($posts->count() === 0)
                    <p class="text-sm text-slate-500">No posts yet.</p>
                @else
                    <div class="space-y-8">
                        @foreach ($posts as $post)
                            @php
                                $publishedAt = $post->published_at ?? $post->created_at;
                                $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
                            @endphp

                            <article class="space-y-3 rounded-3xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-200/60">
                                <h2 class="text-2xl font-semibold text-slate-900">
                                    <a
                                        class="hover:text-brand-600"
                                        href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <div class="flex flex-wrap gap-x-4 text-sm text-slate-500">
                                    @if ($publishedLabel !== '')
                                        <span>{{ $publishedLabel }}</span>
                                    @endif

                                    @if ($post->author)
                                        <span>By {{ $post->author->name ?: 'Author #' . $post->author->id }}</span>
                                    @endif
                                </div>
                                <div>
                                    <a
                                        class="text-sm font-semibold text-brand-600"
                                        href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                        Read post
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="text-sm text-slate-500">
                        {{ $posts->links() }}
                    </div>
                @endif
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
