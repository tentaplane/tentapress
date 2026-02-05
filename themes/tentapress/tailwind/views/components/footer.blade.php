@props([
    'title' => 'TentaPress',
    'tagline' => 'Launch client sites in days, not weeks.',
])

<footer class="border-t border-black/8 bg-page">
    <div class="mx-auto w-full max-w-7xl px-6 py-16">
        <div class="grid gap-12 lg:grid-cols-[1.4fr_repeat(3,1fr)] lg:gap-8">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-surface-900 text-xs font-semibold text-white">TP</span>
                    <span class="font-display text-lg font-semibold text-surface-900">{{ $title }}</span>
                </div>
                <p class="max-w-xs text-sm leading-relaxed text-surface-600">{{ $tagline }}</p>
                <div class="flex flex-wrap items-center gap-3 text-sm text-surface-500">
                    <span>hello@youragency.com</span>
                    <span class="h-1 w-1 rounded-full bg-surface-300"></span>
                    <span>New York · London</span>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-surface-900">Product</div>
                <nav class="mt-4 flex flex-col gap-2.5 text-sm text-surface-600">
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Features</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Blocks library</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Themes</a>
                </nav>
            </div>

            <div>
                <div class="text-sm font-semibold text-surface-900">Resources</div>
                <nav class="mt-4 flex flex-col gap-2.5 text-sm text-surface-600">
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Documentation</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Starter kit</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Community</a>
                </nav>
            </div>

            <div>
                <div class="text-sm font-semibold text-surface-900">Company</div>
                <nav class="mt-4 flex flex-col gap-2.5 text-sm text-surface-600">
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">About</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Press</a>
                    <a class="w-fit rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Careers</a>
                </nav>
            </div>
        </div>

        <div class="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-black/8 pt-8 text-sm text-surface-500">
            <span>&copy; {{ date('Y') }} {{ $title }}. All rights reserved.</span>
            <div class="flex flex-wrap gap-6">
                <a class="rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Privacy</a>
                <a class="rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="#">Terms</a>
                <a class="rounded transition-colors hover:text-surface-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-surface-900" href="/admin">Admin</a>
            </div>
        </div>
    </div>
</footer>
