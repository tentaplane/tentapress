<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>TentaPress</title>

        @tpPluginStyles('tentapress/admin-shell')
        @tpPluginScripts('tentapress/admin-shell')
    </head>
    <body class="flex min-h-screen items-center justify-center bg-[#f0f0f1] p-6 text-[#1d2327]">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="text-2xl font-semibold">TentaPress</div>
            </div>

            @include('tentapress-admin::partials.notices')

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
