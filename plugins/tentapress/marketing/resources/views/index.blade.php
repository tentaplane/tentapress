@extends('tentapress-admin::layouts.shell')

@section('title', 'Marketing')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Marketing</h1>
            <p class="tp-description">Manage analytics providers, consent, and marketing script output.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('tp.marketing.update') }}" class="space-y-6">
        @csrf

        <div class="tp-metabox">
            <div class="tp-metabox__title">Consent</div>
            <div class="tp-metabox__body space-y-5">
                <label class="flex items-center gap-3 text-sm font-medium text-slate-900">
                    <input type="checkbox" name="consent_enabled" value="1" @checked(old('consent_enabled', $consent['enabled'])) />
                    Enable analytics consent gating
                </label>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="tp-field">
                        <label class="tp-label">Cookie name</label>
                        <input
                            name="consent_cookie_name"
                            class="tp-input"
                            value="{{ old('consent_cookie_name', $consent['cookie_name']) }}" />
                    </div>
                    <div class="tp-field">
                        <label class="tp-label">Cookie max age (days)</label>
                        <input
                            name="consent_cookie_max_age_days"
                            class="tp-input"
                            value="{{ old('consent_cookie_max_age_days', $consent['cookie_max_age_days']) }}" />
                    </div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Banner title</label>
                    <input name="consent_banner_title" class="tp-input" value="{{ old('consent_banner_title', $consent['banner_title']) }}" />
                </div>

                <div class="tp-field">
                    <label class="tp-label">Banner body</label>
                    <textarea name="consent_banner_body" class="tp-textarea" rows="3">{{ old('consent_banner_body', $consent['banner_body']) }}</textarea>
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
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">Providers</div>
            <div class="tp-metabox__body space-y-6">
                @foreach ($providers as $provider)
                    <div class="rounded-lg border border-black/10 bg-white p-5 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-base font-semibold text-slate-900">{{ $provider['label'] }}</div>
                                <div class="tp-help">{{ $provider['description'] }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($provider['configured'])
                                    <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">Configured</span>
                                @else
                                    <span class="tp-muted text-xs">Not configured</span>
                                @endif
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-900">
                                    <input
                                        type="checkbox"
                                        name="provider_{{ $provider['key'] }}_enabled"
                                        value="1"
                                        @checked(old('provider_'.$provider['key'].'_enabled', $provider['enabled'])) />
                                    Enabled
                                </label>
                            </div>
                        </div>

                        @include('tentapress-marketing::partials.provider-fields', ['provider' => $provider])
                    </div>
                @endforeach
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">Custom Scripts</div>
            <div class="tp-metabox__body space-y-6">
                <div class="tp-help">
                    Custom scripts are rendered raw. Treat this screen as trusted admin-only script injection.
                </div>

                @foreach ($slots as $slotKey => $slot)
                    @php($fieldKey = str_replace('-', '_', $slotKey))
                    <div class="rounded-lg border border-black/10 bg-white p-5 space-y-4">
                        <div>
                            <div class="text-base font-semibold text-slate-900">{{ $slot['label'] }}</div>
                            <div class="tp-help">{{ $slot['help'] }}</div>
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Script content</label>
                            <textarea
                                name="custom_script_{{ $fieldKey }}"
                                class="tp-textarea tp-code"
                                rows="7">{{ old('custom_script_'.$fieldKey, $slot['code']) }}</textarea>
                        </div>

                        <label class="flex items-center gap-3 text-sm font-medium text-slate-900">
                            <input
                                type="checkbox"
                                name="custom_script_{{ $fieldKey }}_analytics_consent"
                                value="1"
                                @checked(old('custom_script_'.$fieldKey.'_analytics_consent', $slot['analytics_consent'])) />
                            Require analytics consent before rendering this slot
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="tp-button-primary">Save changes</button>
        </div>
    </form>
@endsection
