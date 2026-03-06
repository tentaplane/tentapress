@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirect Policy')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirect Policy</h1>
            <p class="tp-description">Configure how slug changes produce redirects.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.redirects.settings') }}" class="space-y-5">
                @csrf
                <div class="tp-field">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="auto_apply_slug_redirects" value="1" @checked($autoApplySlugRedirects) />
                        <span>Auto-apply slug-change redirects</span>
                    </label>
                    <div class="tp-help">When disabled, slug changes create review suggestions instead of active redirects.</div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Save policy</button>
                    <a href="{{ route('tp.redirects.suggestions.index') }}" class="tp-button-secondary">Suggestion queue</a>
                </div>
            </form>
        </div>
    </div>
@endsection
