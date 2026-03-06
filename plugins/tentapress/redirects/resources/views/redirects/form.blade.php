@extends('tentapress-admin::layouts.shell')

@section('title', $redirect ? 'Edit Redirect' : 'Add Redirect')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $redirect ? 'Edit Redirect' : 'Add Redirect' }}</h1>
            <p class="tp-description">Create and validate redirects before publishing.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST"
                action="{{ $redirect ? route('tp.redirects.update', ['redirect' => $redirect->id]) : route('tp.redirects.store') }}"
                class="space-y-5">
                @csrf
                @if ($redirect)
                    @method('PUT')
                @endif

                <div class="tp-field">
                    <label class="tp-label">Source path</label>
                    <input name="source_path" class="tp-input"
                        value="{{ old('source_path', $redirect?->source_path) }}" placeholder="/old-path" required />
                    @error('source_path')
                        <p class="tp-help text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tp-field">
                    <label class="tp-label">Target path</label>
                    <input name="target_path" class="tp-input"
                        value="{{ old('target_path', $redirect?->target_path) }}" placeholder="/new-path" required />
                    @error('target_path')
                        <p class="tp-help text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="tp-field">
                        <label class="tp-label">Status code</label>
                        <select name="status_code" class="tp-select">
                            <option value="301" @selected((string) old('status_code', $redirect?->status_code ?? '301') === '301')>301 Permanent</option>
                            <option value="302" @selected((string) old('status_code', $redirect?->status_code ?? '301') === '302')>302 Temporary</option>
                        </select>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">State</label>
                        <label class="inline-flex items-center gap-2 mt-2">
                            <input type="checkbox" name="is_enabled" value="1" @checked((bool) old('is_enabled', $redirect?->is_enabled ?? true)) />
                            <span>Enabled</span>
                        </label>
                    </div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Notes</label>
                    <textarea name="notes" class="tp-input" rows="4">{{ old('notes', $redirect?->notes) }}</textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">{{ $redirect ? 'Save changes' : 'Create redirect' }}</button>
                    <a href="{{ route('tp.redirects.index') }}" class="tp-button">Back</a>
                </div>
            </form>
        </div>
    </div>

    @if ($redirect)
        <div class="tp-metabox mt-4">
            <div class="tp-metabox__body">
                <h2 class="tp-section-title mb-3">Recent history</h2>
                <table class="tp-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Source</th>
                            <th>Target</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($events ?? collect()) as $event)
                            <tr>
                                <td>{{ $event->action }}</td>
                                <td><code class="tp-code">{{ $event->source_path }}</code></td>
                                <td><code class="tp-code">{{ $event->target_path }}</code></td>
                                <td>{{ $event->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
