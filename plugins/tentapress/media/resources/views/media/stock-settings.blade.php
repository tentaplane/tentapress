@extends('tentapress-admin::layouts.shell')

@section('title', 'Stock Library Settings')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Stock Library Settings</h1>
            <p class="tp-description">Configure providers and API keys for the Media stock library.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.stock') }}" class="tp-button-secondary">Back to Stock Library</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.media.stock.settings.update') }}" class="space-y-5">
                @csrf

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="tp-panel space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">Unsplash</p>
                                <p class="text-xs text-black/60">Photos with required attribution</p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="stock_unsplash_enabled"
                                    value="1"
                                    class="tp-checkbox"
                                    @checked(old('stock_unsplash_enabled', $stockUnsplashEnabled) === '1') />
                                Enabled
                            </label>
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">Access key</label>
                            <input
                                type="password"
                                name="stock_unsplash_key"
                                class="tp-input"
                                value="{{ old('stock_unsplash_key', $stockUnsplashKey) }}" />
                            <div class="tp-help mt-1">
                                Create a demo Unsplash application and use your Unsplash Access Key.
                                <br /><a class="tp-button-link" href="https://unsplash.com/documentation#creating-a-developer-account" target="_blank" rel="noopener">Unsplash documentation</a>
                            </div>
                        </div>
                    </div>

                    <div class="tp-panel space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">Pexels</p>
                                <p class="text-xs text-black/60">Photos, videos</p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="stock_pexels_enabled"
                                    value="1"
                                    class="tp-checkbox"
                                    @checked(old('stock_pexels_enabled', $stockPexelsEnabled) === '1') />
                                Enabled
                            </label>
                        </div>

                        <div class="tp-field">
                            <label class="tp-label">API key</label>
                            <input
                                type="password"
                                name="stock_pexels_key"
                                class="tp-input"
                                value="{{ old('stock_pexels_key', $stockPexelsKey) }}" />
                            <div class="tp-help">
                                <a class="tp-button-link" href="https://www.pexels.com/api/key/" target="_blank" rel="noopener">Your Pexels API Key</a>
                            </div>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                name="stock_pexels_video_enabled"
                                value="1"
                                class="tp-checkbox"
                                @checked(old('stock_pexels_video_enabled', $stockPexelsVideoEnabled) === '1') />
                            Enable video results
                        </label>
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="stock_attribution_reminder"
                        value="1"
                        class="tp-checkbox"
                        @checked(old('stock_attribution_reminder', $stockAttributionReminder) === '1') />
                    Show attribution reminder in stock results
                </label>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save stock settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
