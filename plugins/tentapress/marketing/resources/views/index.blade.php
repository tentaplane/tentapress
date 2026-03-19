@extends('tentapress-admin::layouts.shell')

@section('title', 'Marketing')

@section('content')
    @php
        $enabledProviders = collect($providers)->filter(fn (array $provider): bool => (bool) $provider['enabled']);
        $configuredProviders = collect($providers)->filter(fn (array $provider): bool => (bool) $provider['configured']);
        $customizedSlots = collect($slots)->filter(fn (array $slot): bool => trim((string) $slot['code']) !== '');
        $consentEnabled = (bool) $consent['enabled'];
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Marketing</h1>
            <p class="tp-description">Manage analytics providers, consent, and advanced script output.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('tp.marketing.update') }}" class="space-y-6">
        @csrf

        <div class="tp-metabox">
            <div class="tp-metabox__title">
                <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        Overview
                        <div class="tp-help mt-1">Start here, then update providers, consent, or advanced scripts below.</div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="#marketing-providers" class="tp-button-secondary">Providers</a>
                        <a href="#marketing-consent" class="tp-button-secondary">Consent</a>
                        <a href="#marketing-scripts" class="tp-button-secondary">Custom scripts</a>
                    </div>
                </div>
            </div>

            <div class="tp-metabox__body space-y-5">
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="tp-panel space-y-2">
                        <div class="tp-muted text-xs font-semibold uppercase tracking-[0.18em]">Consent</div>
                        <div class="text-base font-semibold text-slate-900">{{ $consentEnabled ? 'Enabled' : 'Disabled' }}</div>
                        <div class="tp-help">
                            {{ $consentEnabled ? 'Analytics output waits for a consent decision.' : 'Providers and scripts render immediately.' }}
                        </div>
                    </div>

                    <div class="tp-panel space-y-2">
                        <div class="tp-muted text-xs font-semibold uppercase tracking-[0.18em]">Providers</div>
                        <div class="text-base font-semibold text-slate-900">{{ $enabledProviders->count() }} enabled</div>
                        <div class="tp-help">
                            {{ $configuredProviders->count() }} configured{{ $configuredProviders->isNotEmpty() ? ': '.$configuredProviders->pluck('label')->join(', ') : '' }}
                        </div>
                    </div>

                    <div class="tp-panel space-y-2">
                        <div class="tp-muted text-xs font-semibold uppercase tracking-[0.18em]">Custom scripts</div>
                        <div class="text-base font-semibold text-slate-900">{{ $customizedSlots->count() }} populated slot{{ $customizedSlots->count() === 1 ? '' : 's' }}</div>
                        <div class="tp-help">
                            {{ $customizedSlots->isNotEmpty() ? $customizedSlots->pluck('label')->join(', ') : 'No raw script injections configured.' }}
                        </div>
                    </div>
                </div>

                <div class="tp-notice-info mb-0">
                    Provider settings are the preferred path. Use custom scripts only when a provider integration or theme hook is not enough.
                </div>
            </div>
        </div>

        <div id="marketing-providers" class="tp-metabox">
            <div class="tp-metabox__title">
                <div>
                    Providers
                    <div class="tp-help mt-1">Enable only the providers you actually use. Open a provider to review or complete its settings.</div>
                </div>
            </div>

            <div class="tp-metabox__body space-y-4">
                @foreach ($providers as $provider)
                    @php
                        $fieldValues = collect($provider['fields'])->filter(fn (array $field): bool => trim((string) ($field['value'] ?? '')) !== '');
                        $shouldOpen = (bool) $provider['enabled'] || ! (bool) $provider['configured'];
                    @endphp

                    <details class="rounded border border-black/10 bg-white" @if ($shouldOpen) open @endif>
                        <summary class="flex cursor-pointer list-none flex-col gap-3 px-4 py-4 marker:hidden sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-base font-semibold text-slate-900">{{ $provider['label'] }}</div>

                                    @if ($provider['configured'])
                                        <span class="tp-badge tp-badge-success">Configured</span>
                                    @else
                                        <span class="tp-badge border-amber-200 bg-amber-50 text-amber-800">Needs setup</span>
                                    @endif

                                    @if ($provider['enabled'])
                                        <span class="tp-badge tp-badge-info">Enabled</span>
                                    @else
                                        <span class="tp-badge border-slate-200 bg-slate-50 text-slate-700">Inactive</span>
                                    @endif
                                </div>

                                <div class="tp-help max-w-2xl">{{ $provider['description'] }}</div>

                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="rounded border border-black/10 bg-[#f6f7f7] px-2 py-1 text-black/70">
                                        {{ $fieldValues->count() }} field{{ $fieldValues->count() === 1 ? '' : 's' }} populated
                                    </span>
                                    @if ($consentEnabled)
                                        <span class="rounded border border-black/10 bg-[#f6f7f7] px-2 py-1 text-black/70">
                                            Waits for analytics consent
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <input
                                    type="checkbox"
                                    name="provider_{{ $provider['key'] }}_enabled"
                                    value="1"
                                    @checked(old('provider_'.$provider['key'].'_enabled', $provider['enabled'])) />
                                Enabled
                            </label>
                        </summary>

                        <div class="border-t border-black/10 bg-[#f6f7f7] px-4 py-4">
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
                                <div class="space-y-4">
                                    @include('tentapress-marketing::partials.provider-fields', ['provider' => $provider])
                                </div>

                                <div class="tp-panel space-y-3 text-sm">
                                    <div>
                                        <div class="font-semibold text-slate-900">Runtime</div>
                                        <div class="tp-help mt-1">{{ $provider['enabled'] ? 'Will render when configured.' : 'Will stay inactive until enabled.' }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">Consent</div>
                                        <div class="tp-help mt-1">{{ $consentEnabled ? 'Blocked until analytics are allowed.' : 'Renders without a consent gate.' }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">Advanced overrides</div>
                                        <div class="tp-help mt-1">Only use script URL overrides if you self-host or proxy the provider script.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        <div id="marketing-consent" class="tp-metabox">
            <div class="tp-metabox__title">
                <div>
                    Consent
                    <div class="tp-help mt-1">Control when analytics can render and preview the copy visitors will see.</div>
                </div>
            </div>

            <div class="tp-metabox__body space-y-5">
                <label class="flex items-start gap-3 rounded border border-sky-200 bg-sky-50 px-4 py-4">
                    <input type="checkbox" name="consent_enabled" value="1" @checked(old('consent_enabled', $consent['enabled'])) />
                    <span class="space-y-1">
                        <span class="block text-sm font-semibold text-slate-900">Enable analytics consent gating</span>
                        <span class="block text-sm text-slate-600">
                            When enabled, providers and consent-gated custom scripts wait until the visitor explicitly allows analytics.
                        </span>
                    </span>
                </label>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div class="tp-field">
                                <label class="tp-label">Cookie name</label>
                                <input
                                    name="consent_cookie_name"
                                    class="tp-input"
                                    value="{{ old('consent_cookie_name', $consent['cookie_name']) }}" />
                                <div class="tp-help">Stored on the public site to remember the visitor’s analytics choice.</div>
                            </div>
                            <div class="tp-field">
                                <label class="tp-label">Cookie max age (days)</label>
                                <input
                                    name="consent_cookie_max_age_days"
                                    class="tp-input"
                                    value="{{ old('consent_cookie_max_age_days', $consent['cookie_max_age_days']) }}" />
                                <div class="tp-help">How long the saved consent decision should persist.</div>
                            </div>
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Banner title</label>
                            <input name="consent_banner_title" class="tp-input" value="{{ old('consent_banner_title', $consent['banner_title']) }}" />
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Banner body</label>
                            <textarea name="consent_banner_body" class="tp-textarea" rows="4">{{ old('consent_banner_body', $consent['banner_body']) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <div class="tp-field">
                                <label class="tp-label">Accept button</label>
                                <input name="consent_accept_label" class="tp-input" value="{{ old('consent_accept_label', $consent['accept_label']) }}" />
                            </div>
                            <div class="tp-field">
                                <label class="tp-label">Reject button</label>
                                <input name="consent_reject_label" class="tp-input" value="{{ old('consent_reject_label', $consent['reject_label']) }}" />
                            </div>
                            <div class="tp-field">
                                <label class="tp-label">Manage button</label>
                                <input name="consent_manage_label" class="tp-input" value="{{ old('consent_manage_label', $consent['manage_label']) }}" />
                            </div>
                            <div class="tp-field">
                                <label class="tp-label">Privacy shortcut label</label>
                                <input name="consent_privacy_button_label" class="tp-input" value="{{ old('consent_privacy_button_label', $consent['privacy_button_label']) }}" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="tp-panel space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Banner preview</div>
                                    <div class="tp-help mt-1">Public consent prompt</div>
                                </div>

                                @if ($consentEnabled)
                                    <span class="tp-badge tp-badge-success">Active</span>
                                @else
                                    <span class="tp-badge border-slate-200 bg-slate-50 text-slate-700">Disabled</span>
                                @endif
                            </div>

                            <div class="rounded border border-black/10 bg-white p-4 space-y-3">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ old('consent_banner_title', $consent['banner_title']) }}</div>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        {{ old('consent_banner_body', $consent['banner_body']) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="tp-button-primary pointer-events-none min-h-0 px-3 py-2">{{ old('consent_accept_label', $consent['accept_label']) }}</span>
                                    <span class="tp-button-secondary pointer-events-none min-h-0 px-3 py-2">{{ old('consent_reject_label', $consent['reject_label']) }}</span>
                                    <span class="tp-button-secondary pointer-events-none min-h-0 px-3 py-2">{{ old('consent_manage_label', $consent['manage_label']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="tp-panel space-y-2">
                            <div class="text-sm font-semibold text-slate-900">Preferences shortcut</div>
                            <div class="inline-flex">
                                <span class="tp-button-secondary pointer-events-none min-h-0 px-3 py-2">{{ old('consent_privacy_button_label', $consent['privacy_button_label']) }}</span>
                            </div>
                            <div class="tp-help">This appears after the visitor has already made a choice.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="marketing-scripts" class="tp-metabox">
            <div class="tp-metabox__title">
                <div>
                    Advanced
                    <div class="tp-help mt-1">Use raw scripts only when provider settings are not enough.</div>
                </div>
            </div>

            <div class="tp-metabox__body space-y-4">
                <details>
                    <summary class="cursor-pointer text-sm font-semibold text-slate-900">Custom scripts</summary>

                    <div class="mt-4 space-y-4">
                        <div class="tp-notice-warning mb-0">
                            Scripts entered here are rendered raw on the public site. Only use this area for trusted admin-managed code.
                        </div>

                        @foreach ($slots as $slotKey => $slot)
                            @php
                                $fieldKey = str_replace('-', '_', $slotKey);
                                $slotHasCode = trim((string) $slot['code']) !== '';
                            @endphp

                            <details class="rounded border border-black/10 bg-white" @if ($slotHasCode) open @endif>
                                <summary class="flex cursor-pointer list-none flex-col gap-3 px-4 py-4 marker:hidden sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-base font-semibold text-slate-900">{{ $slot['label'] }}</div>
                                            @if ($slotHasCode)
                                                <span class="tp-badge tp-badge-info">Configured</span>
                                            @else
                                                <span class="tp-badge border-slate-200 bg-slate-50 text-slate-700">Empty</span>
                                            @endif
                                            @if ($slot['analytics_consent'])
                                                <span class="tp-badge border-amber-200 bg-amber-50 text-amber-800">Consent gated</span>
                                            @endif
                                        </div>
                                        <div class="tp-help max-w-2xl">{{ $slot['help'] }}</div>
                                    </div>
                                </summary>

                                <div class="border-t border-black/10 bg-[#f6f7f7] px-4 py-4 space-y-4">
                                    <div class="tp-field">
                                        <label class="tp-label">Script content</label>
                                        <textarea
                                            name="custom_script_{{ $fieldKey }}"
                                            class="tp-textarea tp-code"
                                            rows="8">{{ old('custom_script_'.$fieldKey, $slot['code']) }}</textarea>
                                    </div>

                                    <label class="flex items-start gap-3 text-sm font-medium text-slate-900">
                                        <input
                                            type="checkbox"
                                            name="custom_script_{{ $fieldKey }}_analytics_consent"
                                            value="1"
                                            @checked(old('custom_script_'.$fieldKey.'_analytics_consent', $slot['analytics_consent'])) />
                                        <span class="space-y-1">
                                            <span class="block">Require analytics consent before rendering this slot</span>
                                            <span class="block text-sm font-normal text-slate-600">
                                                Turn this on for analytics or tracking snippets. Leave it off for non-tracking markup that must always render.
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </details>
                        @endforeach
                    </div>
                </details>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="tp-button-primary">Save changes</button>
        </div>
    </form>
@endsection
