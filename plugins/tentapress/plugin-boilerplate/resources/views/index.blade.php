@extends('tentapress-admin::layouts.shell')

@section('title', 'Plugin Boilerplate')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Plugin Boilerplate</h1>
            <p class="tp-description">Use this starter as the baseline for new first-party TentaPress plugins.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.plugin-boilerplate.update') }}" class="space-y-5">
                @csrf

                <div class="tp-field">
                    <label class="tp-label" for="plugin_enabled">Plugin enabled</label>
                    <input
                        id="plugin_enabled"
                        name="plugin_enabled"
                        type="checkbox"
                        value="1"
                        class="tp-checkbox"
                        @checked(old('plugin_enabled', $pluginEnabled))
                    />
                    <div class="tp-help">Simple example setting stored via the shared settings plugin.</div>
                </div>

                <div class="tp-field">
                    <label class="tp-label" for="endpoint_prefix">Endpoint prefix</label>
                    <input
                        id="endpoint_prefix"
                        name="endpoint_prefix"
                        class="tp-input"
                        value="{{ old('endpoint_prefix', $endpointPrefix) }}"
                    />
                    <div class="tp-help">Use kebab-case values such as <code class="tp-code">my-plugin</code>.</div>
                </div>

                <div class="tp-field">
                    <label class="tp-label" for="admin_notice">Admin notice</label>
                    <textarea id="admin_notice" name="admin_notice" class="tp-textarea" rows="4">{{ old('admin_notice', $adminNotice) }}</textarea>
                    <div class="tp-help">Example content field for a plugin-owned admin view.</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
