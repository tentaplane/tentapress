<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>TentaPress</title>

        @vite(['plugins/tentapress/admin-shell/resources/css/admin.css', 'plugins/tentapress/admin-shell/resources/js/admin.js'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-[#f0f0f1] p-6 text-[#1d2327]">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="text-2xl font-semibold">TentaPress</div>
            </div>

            @if (session('tp_notice_success'))
                <div class="tp-notice-success">{{ session('tp_notice_success') }}</div>
            @endif

            @if (session('tp_notice_error'))
                <div class="tp-notice-error">{{ session('tp_notice_error') }}</div>
            @endif

            @if (session('tp_notice_warning'))
                <div class="tp-notice-warning">{{ session('tp_notice_warning') }}</div>
            @endif

            @if (session('tp_notice_info'))
                <div class="tp-notice-info">{{ session('tp_notice_info') }}</div>
            @endif

            @if ($errors->any())
                <div class="tp-notice-error">
                    <div class="mb-1 font-semibold">Login failed.</div>
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="tp-metabox">
                <div class="tp-metabox__title">Log In</div>
                <div class="tp-metabox__body">
                    <form method="POST" action="{{ route('tp.login.submit') }}" class="space-y-4">
                        @csrf

                        <div class="tp-field">
                            <label class="tp-label">Email</label>
                            <input
                                name="email"
                                type="email"
                                class="tp-input"
                                value="{{ old('email') }}"
                                required
                                autofocus />
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Password</label>
                            <input name="password" type="password" class="tp-input" required />
                        </div>

                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                class="tp-checkbox"
                                name="remember"
                                value="1"
                                @checked(old('remember')) />
                            <span class="tp-muted text-sm">Remember me</span>
                        </label>

                        <div class="flex items-center justify-between gap-2">
                            <button type="submit" class="tp-button-primary w-full justify-center">Log In</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tp-muted mt-6 text-center text-xs">&copy; {{ date('Y') }} TentaPress</div>
        </div>
    </body>
</html>
