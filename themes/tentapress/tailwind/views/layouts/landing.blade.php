<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        @include('tentapress-seo::head', ['page' => $page])

        @vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/tentapress/tailwind/build')
    </head>
    <body class="bg-page font-sans text-surface-900 antialiased">
        <div class="relative flex min-h-screen flex-col">
            <x-tp-theme::header />

            <main class="relative z-10 flex-1">
                <article class="tp-page-content {{ ! empty($isPageEditorContent) ? 'tp-page-content--page' : '' }}">
                    {!! $blocksHtml !!}
                </article>
            </main>

            <x-tp-theme::footer />
        </div>
    </body>
</html>
