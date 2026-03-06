@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirects')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirects</h1>
            <p class="tp-description">Manage permalink redirects and prevent broken links.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.redirects.suggestions.index') }}" class="tp-button">Suggestions</a>
            <a href="{{ route('tp.redirects.settings') }}" class="tp-button">Policy</a>
            <a href="{{ route('tp.redirects.create') }}" class="tp-button-primary">Add redirect</a>
        </div>
    </div>

    <div class="tp-metabox mb-4">
        <div class="tp-metabox__body">
            <form method="GET" action="{{ route('tp.redirects.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <input type="text" name="q" class="tp-input md:col-span-2" placeholder="Search source or target"
                    value="{{ $search }}" />
                <select name="status_code" class="tp-select">
                    <option value="">All status codes</option>
                    <option value="301" @selected((string) $statusCode === '301')>301</option>
                    <option value="302" @selected((string) $statusCode === '302')>302</option>
                </select>
                <select name="enabled" class="tp-select">
                    <option value="">Enabled + Disabled</option>
                    <option value="1" @selected((string) $enabled === '1')>Enabled</option>
                    <option value="0" @selected((string) $enabled === '0')>Disabled</option>
                </select>
                <button type="submit" class="tp-button">Filter</button>
            </form>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <table class="tp-table">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>State</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($redirects as $redirect)
                        <tr>
                            <td><code class="tp-code">{{ $redirect->source_path }}</code></td>
                            <td><code class="tp-code">{{ $redirect->target_path }}</code></td>
                            <td>{{ $redirect->status_code }}</td>
                            <td>{{ $redirect->is_enabled ? 'Enabled' : 'Disabled' }}</td>
                            <td>{{ $redirect->updated_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('tp.redirects.edit', ['redirect' => $redirect->id]) }}" class="tp-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No redirects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">{{ $redirects->links() }}</div>
        </div>
    </div>
@endsection
