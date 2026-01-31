@extends('tentapress-admin::layouts.shell')

@section('title', 'Page not found')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Page not found</h1>
            <p class="tp-description">The page you are looking for could not be found.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">404 Not Found</div>
        <div class="tp-metabox__body space-y-4">
            <div class="tp-muted text-sm">
                {{ $message ?? 'The page you are looking for could not be found.' }}
            </div>

            @if (! empty($path))
                <div class="tp-pre">{{ $path }}</div>
            @endif

            <div class="flex gap-2">
                <a href="/admin" class="tp-button-primary">Go to Dashboard</a>
                <a href="javascript:history.back()" class="tp-button-secondary">Go Back</a>
            </div>
        </div>
    </div>
@endsection
