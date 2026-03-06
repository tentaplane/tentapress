@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirect Suggestions')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirect Suggestions</h1>
            <p class="tp-description">Review generated redirect candidates before publish.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.redirects.index') }}" class="tp-button-secondary">Redirects</a>
            <a href="{{ route('tp.redirects.settings') }}" class="tp-button-secondary">Policy</a>
        </div>
    </div>

    <div class="tp-metabox mb-4">
        <div class="tp-metabox__body">
            <form method="GET" action="{{ route('tp.redirects.suggestions.index') }}" class="flex items-end gap-3">
                <div class="tp-field">
                    <label class="tp-label">State</label>
                    <select class="tp-select" name="state">
                        <option value="pending" @selected($state === 'pending')>Pending</option>
                        <option value="approved" @selected($state === 'approved')>Approved</option>
                        <option value="rejected" @selected($state === 'rejected')>Rejected</option>
                    </select>
                </div>
                <button type="submit" class="tp-button-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--sticky-head">
                    <thead class="tp-table__thead">
                        <tr>
                            <th>Source</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Origin</th>
                            <th>Conflict</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @forelse ($suggestions as $suggestion)
                            <tr class="tp-table__row">
                                <td class="tp-table__td"><code class="tp-code">{{ $suggestion->source_path }}</code></td>
                                <td class="tp-table__td"><code class="tp-code">{{ $suggestion->target_path }}</code></td>
                                <td class="tp-table__td">{{ $suggestion->status_code }}</td>
                                <td class="tp-table__td">{{ $suggestion->origin }}</td>
                                <td class="tp-table__td">{{ $suggestion->conflict_type ?: 'None' }}</td>
                                <td class="tp-table__td">
                                    @if ($suggestion->state === 'pending')
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('tp.redirects.suggestions.approve', ['suggestion' => $suggestion->id]) }}">
                                                @csrf
                                                <button type="submit" class="tp-button-primary">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('tp.redirects.suggestions.reject', ['suggestion' => $suggestion->id]) }}">
                                                @csrf
                                                <button type="submit" class="tp-button-secondary">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        {{ ucfirst($suggestion->state) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="tp-table__row">
                                <td class="tp-table__td" colspan="6">No suggestions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $suggestions->links() }}</div>
        </div>
    </div>
@endsection
