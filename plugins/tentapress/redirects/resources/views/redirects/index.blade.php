@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirects')

@section('content')
    @php
        $enabledFilter = in_array((string) $enabled, ['', '0', '1'], true) ? (string) $enabled : '';
        $statusCodeFilter = in_array((string) $statusCode, ['', '301', '302'], true) ? (string) $statusCode : '';
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Redirects</h1>
            <p class="tp-description">Manage permalink redirects and prevent broken links.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.redirects.suggestions.index') }}" class="tp-button-secondary">Suggestions</a>
            <a href="{{ route('tp.redirects.settings') }}" class="tp-button-secondary">Policy</a>
            <a href="{{ route('tp.redirects.create') }}" class="tp-button-primary">Add redirect</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.redirects.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('tp.redirects.index', ['q' => $search !== '' ? $search : null, 'status_code' => $statusCodeFilter !== '' ? $statusCodeFilter : null]) }}"
                        class="{{ $enabledFilter === '' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All
                    </a>
                    <a
                        href="{{ route('tp.redirects.index', ['enabled' => '1', 'q' => $search !== '' ? $search : null, 'status_code' => $statusCodeFilter !== '' ? $statusCodeFilter : null]) }}"
                        class="{{ $enabledFilter === '1' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Enabled
                    </a>
                    <a
                        href="{{ route('tp.redirects.index', ['enabled' => '0', 'q' => $search !== '' ? $search : null, 'status_code' => $statusCodeFilter !== '' ? $statusCodeFilter : null]) }}"
                        class="{{ $enabledFilter === '0' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Disabled
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex flex-wrap gap-2">
                    <label class="sr-only" for="redirects-status-code">Filter by status code</label>
                    <select id="redirects-status-code" name="status_code" class="tp-select w-full sm:w-40">
                        <option value="">All status codes</option>
                        <option value="301" @selected($statusCodeFilter === '301')>301</option>
                        <option value="302" @selected($statusCodeFilter === '302')>302</option>
                    </select>

                    <label class="sr-only" for="redirects-search">Search redirects</label>
                    <input id="redirects-search" type="text" name="q" class="tp-input w-full sm:w-64" placeholder="Search source or target..."
                        value="{{ $search }}" />

                    <input type="hidden" name="enabled" value="{{ $enabledFilter }}" />

                    <button type="submit" class="tp-button-secondary">Search</button>

                    @if ($search !== '' || $statusCodeFilter !== '' || $enabledFilter !== '')
                        <a href="{{ route('tp.redirects.index') }}" class="tp-button-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        @if ($redirects->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No redirects found for the current filters.</div>
        @else
            <div class="tp-metabox__body">
                <form method="POST" action="{{ route('tp.redirects.bulk') }}" class="space-y-4">
                    @csrf
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="sr-only" for="redirects-bulk-action">Bulk action</label>
                        <select id="redirects-bulk-action" name="action" class="tp-select w-full sm:w-56">
                            <option value="enable">Enable selected</option>
                            <option value="disable">Disable selected</option>
                        </select>
                        <button type="submit" class="tp-button-secondary">Apply</button>
                        <span class="tp-help">Select rows, then apply action.</span>
                    </div>

                    <div class="tp-table-wrap">
                        <table class="tp-table tp-table--responsive tp-table--sticky-head">
                            <thead class="tp-table__thead">
                                <tr>
                                    <th class="tp-table__th">
                                        <input type="checkbox" id="select-all-redirects" />
                                    </th>
                                    <th class="tp-table__th">Source</th>
                                    <th class="tp-table__th">Target</th>
                                    <th class="tp-table__th">Status</th>
                                    <th class="tp-table__th">State</th>
                                    <th class="tp-table__th">Updated</th>
                                    <th class="tp-table__th text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="tp-table__tbody">
                                @foreach ($redirects as $redirect)
                                    <tr class="tp-table__row">
                                        <td data-label="Select" class="tp-table__td align-middle py-4">
                                            <input type="checkbox" name="ids[]" value="{{ $redirect->id }}" class="redirect-select-item" />
                                        </td>
                                        <td data-label="Source" class="tp-table__td align-middle py-4">
                                            <code class="tp-code">{{ $redirect->source_path }}</code>
                                        </td>
                                        <td data-label="Target" class="tp-table__td align-middle py-4">
                                            <code class="tp-code">{{ $redirect->target_path }}</code>
                                        </td>
                                        <td data-label="Status" class="tp-table__td align-middle py-4">
                                            <span class="tp-code">{{ $redirect->status_code }}</span>
                                        </td>
                                        <td data-label="State" class="tp-table__td align-middle py-4">
                                            @if ($redirect->is_enabled)
                                                <span class="tp-badge tp-badge-success">Enabled</span>
                                            @else
                                                <span class="tp-badge tp-badge-info">Disabled</span>
                                            @endif
                                        </td>
                                        <td data-label="Updated" class="tp-table__td tp-muted align-middle py-4">
                                            {{ $redirect->updated_at?->diffForHumans() ?? '—' }}
                                        </td>
                                        <td data-label="Actions" class="tp-table__td align-middle py-4">
                                            <div class="tp-muted flex justify-end gap-3 text-xs">
                                                <a href="{{ route('tp.redirects.edit', ['redirect' => $redirect->id]) }}" class="tp-button-link">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="tp-metabox__body">{{ $redirects->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const selectAll = document.getElementById('select-all-redirects');
            if (!selectAll) {
                return;
            }

            selectAll.addEventListener('change', () => {
                document.querySelectorAll('.redirect-select-item').forEach((node) => {
                    node.checked = selectAll.checked;
                });
            });
        })();
    </script>
@endpush
