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
                    <button type="button" class="tp-button" id="redirect-run-diagnostics">Run diagnostics</button>
                    <button type="submit" class="tp-button-primary">{{ $redirect ? 'Save changes' : 'Create redirect' }}</button>
                    <a href="{{ route('tp.redirects.index') }}" class="tp-button">Back</a>
                </div>

                <div id="redirect-diagnostics" class="tp-help"></div>
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

@push('scripts')
    <script>
        (() => {
            const diagnosticsButton = document.getElementById('redirect-run-diagnostics');
            const diagnosticsOutput = document.getElementById('redirect-diagnostics');
            const form = diagnosticsButton?.closest('form');
            if (!diagnosticsButton || !diagnosticsOutput || !form) {
                return;
            }

            diagnosticsButton.addEventListener('click', async () => {
                diagnosticsOutput.textContent = 'Running diagnostics...';

                const sourcePath = form.querySelector('[name=\"source_path\"]')?.value || '';
                const targetPath = form.querySelector('[name=\"target_path\"]')?.value || '';
                const statusCode = form.querySelector('[name=\"status_code\"]')?.value || '301';
                const csrfToken = form.querySelector('[name=\"_token\"]')?.value || '';
                const ignoreId = '{{ (string) ($redirect?->id ?? '') }}';

                const payload = {
                    source_path: sourcePath,
                    target_path: targetPath,
                    status_code: Number(statusCode),
                };

                if (ignoreId !== '') {
                    payload.ignore_id = Number(ignoreId);
                }

                const response = await fetch('{{ route('tp.redirects.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const json = await response.json();
                if (json.ok) {
                    diagnosticsOutput.textContent = `Diagnostics passed. Normalized source: ${json.normalized.source_path}; target: ${json.normalized.target_path}`;
                    diagnosticsOutput.classList.remove('text-red-600');
                    diagnosticsOutput.classList.add('text-green-700');
                    return;
                }

                diagnosticsOutput.textContent = json.message || 'Diagnostics failed.';
                diagnosticsOutput.classList.remove('text-green-700');
                diagnosticsOutput.classList.add('text-red-600');
            });
        })();
    </script>
@endpush
