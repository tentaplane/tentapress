<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $entry->title }}</title>
        <style>
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, sans-serif;
                background: #f8fafc;
                color: #0f172a;
            }

            main {
                margin: 0 auto;
                max-width: 56rem;
                padding: 4rem 1.5rem;
            }

            .meta {
                color: #475569;
                font-size: 0.95rem;
            }

            .fields {
                display: grid;
                gap: 1rem;
                margin-top: 2rem;
            }

            .field-card {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                background: #fff;
                padding: 1rem 1.25rem;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="meta">{{ $contentType->singular_label }}@if ($entry->published_at) - {{ $entry->published_at->format('j M Y') }}@endif</div>
            <h1>{{ $entry->title }}</h1>

            {!! $contentHtml !!}
        </main>
    </body>
</html>
