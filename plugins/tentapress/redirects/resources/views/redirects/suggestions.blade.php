@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirect Suggestions')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirect Suggestions</h1>
            <p class="tp-description">Review generated redirect candidates before publish.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.redirects.index') }}" class="tp-button">Redirects</a>
            <a href="{{ route('tp.redirects.settings') }}" class="tp-button">Policy</a>
        </div>
    </div>

    <div class="tp-metabox mb-4">
        <div class="tp-metabox__body">
            <form method="GET" action="{{ route('tp.redirects.suggestions.index') }}" class="flex gap-3 items-end">
                <div class="tp-field">
                    <label class="tp-label">State</label>
                    <select class="tp-select" name="state">
                        <option value="pending" @selected($state === 'pending')>Pending</option>
                        <option value="approved" @selected($state === 'approved')>Approved</option>
                        <option value="rejected" @selected($state === 'rejected')>Rejected</option>
                    </select>
                </div>
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
                        <th>Origin</th>
                        <th>Conflict</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suggestions as $suggestion)
                        <tr>
                            <td><code class="tp-code">{{ $suggestion->source_path }}</code></td>
                            <td><code class="tp-code">{{ $suggestion->target_path }}</code></td>
                            <td>{{ $suggestion->status_code }}</td>
                            <td>{{ $suggestion->origin }}</td>
                            <td>{{ $suggestion->conflict_type ?: 'None' }}</td>
                            <td>
                                @if ($suggestion->state === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('tp.redirects.suggestions.approve', ['suggestion' => $suggestion->id]) }}">
                                            @csrf
                                            <button type="submit" class="tp-button-primary">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('tp.redirects.suggestions.reject', ['suggestion' => $suggestion->id]) }}">
                                            @csrf
                                            <button type="submit" class="tp-button">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    {{ ucfirst($suggestion->state) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No suggestions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">{{ $suggestions->links() }}</div>
        </div>
    </div>
@endsection
