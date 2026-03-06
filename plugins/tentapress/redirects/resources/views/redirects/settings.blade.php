@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirect Policy')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirect Policy</h1>
            <p class="tp-description">Configure how slug changes become redirects or suggestions.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.redirects.index') }}" class="tp-button-secondary">Redirects</a>
            <a href="{{ route('tp.redirects.suggestions.index') }}" class="tp-button-secondary">Suggestion queue</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <h2 class="tp-section-title">Slug-change behavior</h2>
        </div>
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.redirects.settings') }}" class="space-y-5">
                @csrf

                <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                    <label class="inline-flex items-start gap-2">
                        <input type="checkbox" name="auto_apply_slug_redirects" value="1" class="mt-1" @checked($autoApplySlugRedirects) />
                        <span>
                            <span class="block font-medium text-black">Auto-apply slug-change redirects</span>
                            <span class="tp-help block mt-1">When disabled, slug changes create pending suggestions for manual review.</span>
                        </span>
                    </label>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button type="submit" class="tp-button-primary">Save policy</button>
                    <a href="{{ route('tp.redirects.suggestions.index') }}" class="tp-button-secondary">Review suggestions</a>
                </div>
            </form>
        </div>
    </div>
@endsection
