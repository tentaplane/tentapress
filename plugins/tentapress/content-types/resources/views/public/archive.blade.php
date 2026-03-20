<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $contentType->plural_label }}</title>
        <style>
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, sans-serif;
                background: #f8fafc;
                color: #0f172a;
            }

            main {
                margin: 0 auto;
                max-width: 64rem;
                padding: 4rem 1.5rem;
            }

            .grid {
                display: grid;
                gap: 1rem;
                margin-top: 2rem;
            }

            .card {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                background: #fff;
                padding: 1.25rem;
            }

            .meta {
                color: #475569;
                font-size: 0.95rem;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="meta">{{ $contentType->singular_label }} archive</div>
            <h1>{{ $contentType->plural_label }}</h1>

            @if ($contentType->description)
                <p>{{ $contentType->description }}</p>
            @endif

            @if ($entries->count() === 0)
                <p class="meta">No {{ strtolower($contentType->plural_label) }} have been published yet.</p>
            @else
                <div class="grid">
                    @foreach ($entries as $entry)
                        <article class="card">
                            <div class="meta">
                                {{ $entry->published_at?->format('j M Y') ?? 'Draft' }}
                            </div>
                            <h2>
                                <a href="{{ $entry->permalink() }}">{{ $entry->title }}</a>
                            </h2>
                        </article>
                    @endforeach
                </div>

                <div style="margin-top: 2rem;">
                    {{ $entries->links() }}
                </div>
            @endif
        </main>
    </body>
</html>
