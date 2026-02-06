@extends('tentapress-admin::layouts.shell')

@section('title', 'Import')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Import</h1>
            <p class="tp-description">Upload an export file from another TentaPress site.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">Upload export file</div>
        <div class="tp-metabox__body space-y-5">
            <form
                method="POST"
                action="{{ route('tp.import.analyze') }}"
                enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div class="tp-field">
                    <label class="tp-label">Export file (.zip)</label>
                    <input type="file" name="bundle" class="tp-input" accept=".zip" required />
                    <div class="tp-help">Choose the `.zip` file created from the Export screen.</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">Review file</button>
                </div>

                <div class="tp-muted text-xs">You'll review everything before any changes are made.</div>
            </form>
        </div>
    </div>
@endsection
