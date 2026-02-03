@props([
    'title' => 'TentaPress',
    'tagline' => 'The fastest way for agencies to ship small sites.',
])

<footer class="border-t border-slate-200/80 bg-white">
    <div class="mx-auto w-full max-w-6xl px-6 py-12">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_repeat(3,1fr)]">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-xs font-semibold text-white">TP</span>
                    <span class="font-display text-base font-semibold text-slate-900">{{ $title }}</span>
                </div>
                <p class="text-sm text-slate-500">{{ $tagline }}</p>
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <span>hello@youragency.com</span>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <span>New York · London</span>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">Product</div>
                <div class="mt-3 flex flex-col gap-2 text-sm text-slate-500">
                    <a class="hover:text-slate-900" href="#">Features</a>
                    <a class="hover:text-slate-900" href="#">Blocks library</a>
                    <a class="hover:text-slate-900" href="#">Themes</a>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">Resources</div>
                <div class="mt-3 flex flex-col gap-2 text-sm text-slate-500">
                    <a class="hover:text-slate-900" href="#">Documentation</a>
                    <a class="hover:text-slate-900" href="#">Starter kit</a>
                    <a class="hover:text-slate-900" href="#">Community</a>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">Company</div>
                <div class="mt-3 flex flex-col gap-2 text-sm text-slate-500">
                    <a class="hover:text-slate-900" href="#">About</a>
                    <a class="hover:text-slate-900" href="#">Press</a>
                    <a class="hover:text-slate-900" href="#">Careers</a>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200/70 pt-6 text-xs text-slate-500">
            <span>&copy; {{ date('Y') }} {{ $title }}. All rights reserved.</span>
            <div class="flex flex-wrap gap-4">
                <a class="hover:text-slate-900" href="#">Privacy</a>
                <a class="hover:text-slate-900" href="#">Terms</a>
                <a class="hover:text-slate-900" href="/admin">Admin</a>
            </div>
        </div>
    </div>
</footer>
