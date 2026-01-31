<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @include('tentapress-seo::head', ['page' => $page])

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

        <main class="mx-auto max-w-5xl">
            @include('tentapress-pages::partials.blocks', [
            'blocks' => $page->blocks,
            ])
        </main>

        <footer class="border-t border-black/10">
            <div class="mx-auto max-w-5xl p-6 text-sm text-black/60">&copy; {{ date('Y') }} TentaPress</div>
        </footer>
    </body>
</html>
