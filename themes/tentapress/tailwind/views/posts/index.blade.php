<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @php
            $seo = app(\TentaPress\Seo\Services\SeoManager::class)->forBlogIndex();
        @endphp

        @include('tentapress-seo::head', ['seo' => $seo])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-green-50 text-zinc-900">
        <header class="border-b border-black/10">
            <div class="mx-auto flex max-w-5xl items-center justify-between p-6">
                <div class="font-semibold">Tailwind Theme - TentaPress</div>
                <x-tp-theme::menu location="primary">
                    <a href="/" class="hover:underline">Home</a>
                </x-tp-theme::menu>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-8 p-6">
            <div class="space-y-2">
                <div class="text-xs font-semibold tracking-[0.2em] text-black/50 uppercase">Blog</div>
                <h1 class="text-3xl font-semibold tracking-tight">Latest posts</h1>
            </div>

            @if ($posts->count() === 0)
                <p class="text-sm text-black/60">No posts yet.</p>
            @else
                <div class="space-y-6">
                    @foreach ($posts as $post)
                        @php
                            $publishedAt = $post->published_at ?? $post->created_at;
                            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
                        @endphp

                        <article class="space-y-2 border-b border-black/10 pb-6">
                            <h2 class="text-2xl font-semibold">
                                <a
                                    class="hover:underline"
                                    href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <div class="flex flex-wrap gap-x-4 text-sm text-black/60">
                                @if ($publishedLabel !== '')
                                    <span>{{ $publishedLabel }}</span>
                                @endif

                                @if ($post->author)
                                    <span>By {{ $post->author->name ?: 'Author #' . $post->author->id }}</span>
                                @endif
                            </div>
                            <div>
                                <a
                                    class="text-sm font-medium text-black/70 hover:text-black"
                                    href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                    Read post
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div>
                    {{ $posts->links() }}
                </div>
            @endif
        </main>

        <footer class="border-t border-black/10">
            <div class="mx-auto max-w-5xl p-6 text-sm text-black/60">&copy; {{ date('Y') }} TentaPress</div>
        </footer>
    </body>
</html>
