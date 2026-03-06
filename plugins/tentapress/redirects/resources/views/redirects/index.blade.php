@extends('tentapress-admin::layouts.shell')

@section('title', 'Redirects')

@section('content')
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
                <button type="submit" class="tp-button-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form method="POST" action="{{ route('tp.redirects.bulk') }}">
                @csrf
                <div class="flex gap-2 mb-3">
                    <select name="action" class="tp-select">
                        <option value="enable">Enable selected</option>
                        <option value="disable">Disable selected</option>
                    </select>
                    <button type="submit" class="tp-button-secondary">Apply</button>
                </div>
                <div class="tp-table-wrap">
                    <table class="tp-table tp-table--sticky-head">
                        <thead class="tp-table__thead">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-redirects" />
                            </th>
                            <th>Source</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>State</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody class="tp-table__tbody">
                        @forelse ($redirects as $redirect)
                            <tr class="tp-table__row">
                                <td class="tp-table__td">
                                    <input type="checkbox" name="ids[]" value="{{ $redirect->id }}" class="redirect-select-item" />
                                </td>
                                <td class="tp-table__td"><code class="tp-code">{{ $redirect->source_path }}</code></td>
                                <td class="tp-table__td"><code class="tp-code">{{ $redirect->target_path }}</code></td>
                                <td class="tp-table__td">{{ $redirect->status_code }}</td>
                                <td class="tp-table__td">{{ $redirect->is_enabled ? 'Enabled' : 'Disabled' }}</td>
                                <td class="tp-table__td">{{ $redirect->updated_at?->format('Y-m-d H:i') }}</td>
                                <td class="tp-table__td">
                                    <a href="{{ route('tp.redirects.edit', ['redirect' => $redirect->id]) }}" class="tp-button-link">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="tp-table__td" colspan="7">No redirects found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-4">{{ $redirects->links() }}</div>
        </div>
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
