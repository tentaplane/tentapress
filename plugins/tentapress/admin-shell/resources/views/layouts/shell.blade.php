<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', 'Admin') - TentaPress</title>

        @stack('head-prepend')
        @vite(['plugins/tentapress/admin-shell/resources/css/admin.css', 'plugins/tentapress/admin-shell/resources/js/admin.js'])
        @stack('head')
    </head>
    @php
        $fullscreen = trim($__env->yieldContent('shell_fullscreen')) === '1';
    @endphp
    <body class="bg-[#f0f0f1] text-[#1d2327] @yield('body_class')">
        <div class="min-h-screen" x-data="{ sidebarOpen: false }">
            @if (! $fullscreen)
                <div
                    class="fixed inset-0 z-30 bg-black/40 md:hidden"
                    x-show="sidebarOpen"
                    x-transition.opacity
                    @click="sidebarOpen = false"></div>

                @include('tentapress-admin::partials.sidebar')
            @endif

            <div class="ml-0 flex min-h-screen flex-1 flex-col {{ $fullscreen ? '' : 'md:ml-64' }}">
                @if (! $fullscreen)
                    @include('tentapress-admin::partials.topbar')
                @endif

                <main class="{{ $fullscreen ? 'flex-1 p-0' : 'flex-1 p-4 lg:p-6' }}">
                    @if ($fullscreen)
                        @yield('content')
                    @else
                        <div class="tp-wrap">
                            @include('tentapress-admin::partials.notices')

                            @yield('content')
                        </div>
                    @endif
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
