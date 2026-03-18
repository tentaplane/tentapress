@php
    $manager = app(\TentaPress\Marketing\Services\MarketingManager::class);
    $shouldRender = $manager->shouldRenderConsent();
    $config = $manager->consentUiConfig();
@endphp

@if ($shouldRender)
    <div
        data-tp-marketing-banner
        class="fixed inset-x-4 bottom-4 z-50 hidden rounded-2xl border border-black/10 bg-white p-5 shadow-2xl lg:left-auto lg:right-4 lg:max-w-md">
        <div class="space-y-4">
            <div class="space-y-2">
                <div class="text-base font-semibold text-slate-900">{{ $config['bannerTitle'] }}</div>
                <p class="text-sm leading-6 text-slate-600">{{ $config['bannerBody'] }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" data-tp-marketing-accept class="tp-button-primary">{{ $config['acceptLabel'] }}</button>
                <button type="button" data-tp-marketing-reject class="tp-button-secondary">{{ $config['rejectLabel'] }}</button>
                <button type="button" data-tp-marketing-manage class="tp-button-secondary">{{ $config['manageLabel'] }}</button>
            </div>
        </div>
    </div>

    <button
        type="button"
        data-tp-marketing-open-preferences
        class="tp-button-secondary fixed bottom-4 left-4 z-40 hidden">
        {{ $config['privacyButtonLabel'] }}
    </button>

    <div data-tp-marketing-modal class="fixed inset-0 z-50 hidden bg-black/40 p-4">
        <div class="mx-auto mt-12 max-w-lg rounded-2xl border border-black/10 bg-white p-6 shadow-2xl">
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $config['manageLabel'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $config['bannerBody'] }}</p>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-black/10 p-4">
                    <input type="checkbox" data-tp-marketing-analytics />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium text-slate-900">Analytics</span>
                        <span class="block text-sm text-slate-600">Allow site analytics providers and analytics-gated custom scripts.</span>
                    </span>
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button" data-tp-marketing-save class="tp-button-primary">{{ $config['manageLabel'] }}</button>
                    <button type="button" data-tp-marketing-close class="tp-button-secondary">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const config = @json($config);
            const cookieName = String(config.cookieName || 'tp_marketing_consent');
            const maxAge = Math.max(parseInt(config.cookieMaxAgeDays || 180, 10), 1) * 86400;
            const banner = document.querySelector('[data-tp-marketing-banner]');
            const modal = document.querySelector('[data-tp-marketing-modal]');
            const openPreferences = document.querySelector('[data-tp-marketing-open-preferences]');
            const analyticsInput = document.querySelector('[data-tp-marketing-analytics]');

            const readCookie = () => {
                const parts = document.cookie.split(';').map((part) => part.trim()).filter(Boolean);
                const match = parts.find((part) => part.startsWith(cookieName + '='));
                if (!match) return null;

                try {
                    return JSON.parse(decodeURIComponent(match.slice(cookieName.length + 1)));
                } catch (_) {
                    return null;
                }
            };

            const writeCookie = (payload) => {
                document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(payload))}; path=/; max-age=${maxAge}; SameSite=Lax`;
            };

            const currentState = () => {
                const payload = readCookie();
                return payload && typeof payload.analytics === 'boolean' ? payload : null;
            };

            const showBanner = () => banner && banner.classList.remove('hidden');
            const hideBanner = () => banner && banner.classList.add('hidden');
            const showOpenPreferences = () => openPreferences && openPreferences.classList.remove('hidden');
            const hideOpenPreferences = () => openPreferences && openPreferences.classList.add('hidden');
            const openModal = () => modal && modal.classList.remove('hidden');
            const closeModal = () => modal && modal.classList.add('hidden');

            const syncUi = () => {
                const state = currentState();
                if (analyticsInput) {
                    analyticsInput.checked = state ? state.analytics === true : false;
                }

                if (state) {
                    hideBanner();
                    showOpenPreferences();
                } else {
                    showBanner();
                    hideOpenPreferences();
                }
            };

            const save = (analytics) => {
                writeCookie({
                    analytics: analytics === true,
                    updated_at: new Date().toISOString(),
                });
                window.location.reload();
            };

            document.querySelector('[data-tp-marketing-accept]')?.addEventListener('click', () => save(true));
            document.querySelector('[data-tp-marketing-reject]')?.addEventListener('click', () => save(false));
            document.querySelector('[data-tp-marketing-manage]')?.addEventListener('click', () => {
                syncUi();
                openModal();
            });
            document.querySelector('[data-tp-marketing-save]')?.addEventListener('click', () => save(analyticsInput?.checked === true));
            document.querySelector('[data-tp-marketing-close]')?.addEventListener('click', closeModal);
            openPreferences?.addEventListener('click', () => {
                syncUi();
                openModal();
            });
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            syncUi();
        })();
    </script>
@endif
