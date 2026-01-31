@extends('tentapress-admin::layouts.shell')

@section('title', 'Access denied')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Access denied</h1>
            <p class="tp-description">You don’t have permission to access this page.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">403 Forbidden</div>
        <div class="tp-metabox__body space-y-4">
            <div class="tp-muted text-sm">
                {{ $message ?? 'You do not have permission to access this page.' }}
            </div>

            <div class="flex gap-2">
                <a href="/admin" class="tp-button-primary">Go to Dashboard</a>
                <a href="javascript:history.back()" class="tp-button-secondary">Go Back</a>
            </div>
        </div>
    </div>
@endsection
