@extends('tentapress-admin::layouts.shell')

@section('title', 'Media Optimizations')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Media Optimizations</h1>
            <p class="tp-description">Control how optimized image URLs are generated.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.media.index') }}" class="tp-button-secondary">Back to Media</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.media.optimizations.update') }}" class="space-y-6">
                @csrf

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-[#1d2327]" for="optimization_provider">Active service</label>
                        <select id="optimization_provider" name="optimization_provider" class="tp-select">
                            <option value="">Select a service</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->key() }}" @selected($optimizationProvider === $provider->key())>
                                    {{ $provider->label() }}
                                </option>
                            @endforeach
                        </select>
                        <div class="tp-help">
                            This service is used when generating optimized image URLs.
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="optimization_enabled"
                            value="1"
                            class="tp-checkbox"
                            @checked(old('optimization_enabled', $optimizationEnabled) === '1') />
                        Enable optimized image URLs
                    </label>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($providerSettings as $providerView)
                        @include($providerView, $providerSettingsData)
                    @endforeach
                </div>

                <div class="rounded-lg border border-black/10 bg-slate-50 p-3 text-xs text-black/60">
                    Optimized URLs are generated only for image requests that use
                    <code class="tp-code">imageUrl</code>.
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save optimization settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
