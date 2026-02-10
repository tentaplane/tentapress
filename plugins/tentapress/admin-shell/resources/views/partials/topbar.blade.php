<header class="flex min-h-14 items-center gap-2 border-b border-black/10 bg-linear-to-r from-white to-[#f6f9fc] px-3 py-2 sm:gap-3 sm:px-4 lg:px-6">
    <button
        type="button"
        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded border border-black/10 text-sm text-black/70 transition hover:bg-black/5 lg:hidden"
        @click="sidebarOpen = !sidebarOpen"
        :aria-expanded="sidebarOpen ? 'true' : 'false'"
        aria-controls="tp-admin-sidebar"
        aria-label="Open or close menu">
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

    <div class="tp-muted min-w-0 flex-1 truncate text-sm">
        {{ $title ?? 'Admin' }}
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="tp-button-secondary hidden sm:inline-flex">
            View site
        </a>

        @auth
            <span class="tp-muted hidden text-sm md:inline">Signed in as {{ auth()->user()->name ?? 'User' }}</span>
            <form method="POST" action="{{ route('tp.logout') }}">
                @csrf
                <button type="submit" class="tp-button-secondary">Sign out</button>
            </form>
        @endauth
    </div>
</header>
