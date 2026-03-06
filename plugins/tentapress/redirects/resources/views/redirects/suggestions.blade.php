@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirect Suggestions')

@section('content')
    @php
        $stateFilter = in_array((string) $state, ['pending', 'approved', 'rejected'], true) ? (string) $state : 'pending';
    @endphp

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

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.redirects.suggestions.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('tp.redirects.suggestions.index', ['state' => 'pending']) }}"
                        class="{{ $stateFilter === 'pending' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Pending
                    </a>
                    <a
                        href="{{ route('tp.redirects.suggestions.index', ['state' => 'approved']) }}"
                        class="{{ $stateFilter === 'approved' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Approved
                    </a>
                    <a
                        href="{{ route('tp.redirects.suggestions.index', ['state' => 'rejected']) }}"
                        class="{{ $stateFilter === 'rejected' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Rejected
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex gap-2">
                    <label class="sr-only" for="suggestions-state">Filter by state</label>
                    <select id="suggestions-state" class="tp-select w-full sm:w-44" name="state">
                        <option value="pending" @selected($stateFilter === 'pending')>Pending</option>
                        <option value="approved" @selected($stateFilter === 'approved')>Approved</option>
                        <option value="rejected" @selected($stateFilter === 'rejected')>Rejected</option>
                    </select>
                    <button type="submit" class="tp-button-secondary">Filter</button>
                </div>
            </form>
        </div>

        @if ($suggestions->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No suggestions found for this state.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive tp-table--sticky-head">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Source</th>
                            <th class="tp-table__th">Target</th>
                            <th class="tp-table__th">Status</th>
                            <th class="tp-table__th">Origin</th>
                            <th class="tp-table__th">Conflict</th>
                            <th class="tp-table__th">State</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($suggestions as $suggestion)
                            <tr class="tp-table__row">
                                <td data-label="Source" class="tp-table__td align-middle py-4">
                                    <code class="tp-code">{{ $suggestion->source_path }}</code>
                                </td>
                                <td data-label="Target" class="tp-table__td align-middle py-4">
                                    <code class="tp-code">{{ $suggestion->target_path }}</code>
                                </td>
                                <td data-label="Status" class="tp-table__td align-middle py-4">
                                    <span class="tp-code">{{ $suggestion->status_code }}</span>
                                </td>
                                <td data-label="Origin" class="tp-table__td align-middle py-4">
                                    <span class="tp-code">{{ $suggestion->origin }}</span>
                                </td>
                                <td data-label="Conflict" class="tp-table__td align-middle py-4">
                                    @if ($suggestion->conflict_type)
                                        <span class="tp-badge tp-badge-warning">{{ $suggestion->conflict_type }}</span>
                                    @else
                                        <span class="tp-muted text-xs">None</span>
                                    @endif
                                </td>
                                <td data-label="State" class="tp-table__td align-middle py-4">
                                    @if ($suggestion->state === 'approved')
                                        <span class="tp-badge tp-badge-success">Approved</span>
                                    @elseif ($suggestion->state === 'rejected')
                                        <span class="tp-badge tp-badge-warning">Rejected</span>
                                    @else
                                        <span class="tp-badge tp-badge-info">Pending</span>
                                    @endif
                                </td>
                                <td data-label="Actions" class="tp-table__td align-middle py-4">
                                    @if ($suggestion->state === 'pending')
                                        <div class="flex justify-end gap-2">
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
                                        <div class="tp-muted flex justify-end text-xs">No actions</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">{{ $suggestions->links() }}</div>
        @endif
    </div>
@endsection
