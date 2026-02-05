<div class="tp-metabox overflow-hidden">
    <div class="relative bg-linear-to-br from-[#2271b1] to-[#135e96] px-6 py-8 text-white">
        <div class="relative z-10">
            <h2 class="text-xl font-semibold">Welcome to TentaPress</h2>
            <p class="mt-2 text-sm text-white/80">Your site is ready. Jump in with the shortcuts below.</p>
        </div>
        <div class="absolute -top-8 -right-8 size-32 rounded-full bg-white/5"></div>
        <div class="absolute -right-4 -bottom-4 size-20 rounded-full bg-white/5"></div>
    </div>

    @if (count($shortcuts) > 0)
        <div class="grid grid-cols-2 gap-px bg-black/5 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($shortcuts as $shortcut)
                <a
                    href="{{ $shortcut['url'] }}"
                    class="flex items-center justify-center bg-white px-4 py-4 text-center text-sm font-medium text-black/70 transition hover:bg-[#2271b1]/5 hover:text-[#2271b1]">
                    {{ $shortcut['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
