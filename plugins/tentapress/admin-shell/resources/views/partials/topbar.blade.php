<header class="flex h-14 items-center gap-3 border-b border-black/10 bg-white px-4 lg:px-6">
    <button
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded border border-black/10 text-sm text-black/70 transition hover:bg-black/5 md:hidden"
        @click="sidebarOpen = !sidebarOpen"
        aria-label="Toggle menu">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="tp-muted flex-1 text-sm">
        {{ $title ?? 'Admin' }}
    </div>

    <div class="flex items-center gap-3">
        @auth
            <span class="tp-muted text-sm">Hello, {{ auth()->user()->name ?? 'User' }}</span>
            <form method="POST" action="{{ route('tp.logout') }}">
                @csrf
                <button type="submit" class="tp-button-secondary">Log out</button>
            </form>
        @endauth
    </div>
</header>
