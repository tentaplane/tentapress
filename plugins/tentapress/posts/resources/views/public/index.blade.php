<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Blog</title>
    </head>

    <body class="bg-white text-slate-900">
        <main class="mx-auto max-w-4xl space-y-10 px-6 py-12">
            <header class="space-y-3">
                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Blog</div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Latest posts</h1>
            </header>

            @if ($posts->count() === 0)
                <p class="text-sm text-slate-500">No posts yet.</p>
            @else
                <div class="space-y-6">
                    @foreach ($posts as $post)
                        @php
                            $publishedAt = $post->published_at ?? $post->created_at;
                            $publishedLabel = $publishedAt?->format('F j, Y') ?? '';
                        @endphp
                        <article class="space-y-2 border-b border-slate-200 pb-6">
                            <h2 class="text-2xl font-semibold text-slate-900">
                                <a
                                    class="hover:text-slate-600"
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
                                    class="text-sm font-medium text-slate-700 hover:text-slate-900"
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
    </body>
</html>
