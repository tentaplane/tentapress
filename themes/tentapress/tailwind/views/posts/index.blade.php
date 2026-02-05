<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        
        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-page font-sans text-surface-900 antialiased">
        <div class="relative flex min-h-screen flex-col">
            <x-tp-theme::header />

            <main class="relative z-10 mx-auto w-full max-w-5xl flex-1 space-y-14 px-6 pb-24 pt-14">
                <div class="space-y-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-surface-500">Blog</div>
                    <h1 class="font-display text-4xl font-bold text-surface-900 sm:text-5xl">Latest posts</h1>
                    <p class="text-base text-surface-600">Agency updates, product notes, and helpful guides.</p>
                </div>

                @if ($posts->count() === 0)
                    <p class="text-sm text-surface-500">No posts yet.</p>
                @else
                    <div class="space-y-6">
                        @foreach ($posts as $post)
                            @php
                                $publishedAt = $post->published_at ?? $post->created_at;
                                $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
                            @endphp

                            <article class="space-y-3 rounded-[2.5rem] border border-black/8 bg-white p-8">
                                <h2 class="font-display text-2xl font-semibold text-surface-900">
                                    <a
                                        class="transition-colors hover:text-surface-600"
                                        href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <div class="flex flex-wrap gap-x-4 text-sm text-surface-500">
                                    @if ($publishedLabel !== '')
                                        <span>{{ $publishedLabel }}</span>
                                    @endif

                                    @if ($post->author)
                                        <span>By {{ $post->author->name ?: 'Author #' . $post->author->id }}</span>
                                    @endif
                                </div>
                                <div>
                                    <a
                                        class="text-sm font-semibold text-surface-900 transition-colors hover:text-surface-600"
                                        href="{{ route('tp.public.posts.show', ['slug' => $post->slug]) }}">
                                        Read post &rarr;
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="text-sm text-surface-500">
                        {{ $posts->links() }}
                    </div>
                @endif
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
