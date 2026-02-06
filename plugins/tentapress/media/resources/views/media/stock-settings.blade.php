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
                    @foreach ($providerSettings as $providerView)
                        @include($providerView, $providerSettingsData)
                    @endforeach
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
