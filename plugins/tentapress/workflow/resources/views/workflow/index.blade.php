@extends('tentapress-admin::layouts.shell')

@section('title', 'Workflow')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Workflow</h1>
            <p class="tp-description">Track editorial ownership, approvals, and scheduled publishing across pages and posts.</p>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tp.workflow.index') }}" class="{{ $filter === 'all' ? 'tp-button-primary' : 'tp-button-secondary' }}">All</a>
                <a href="{{ route('tp.workflow.index', ['filter' => 'mine']) }}" class="{{ $filter === 'mine' ? 'tp-button-primary' : 'tp-button-secondary' }}">Assigned to me</a>
                <a href="{{ route('tp.workflow.index', ['filter' => 'review']) }}" class="{{ $filter === 'review' ? 'tp-button-primary' : 'tp-button-secondary' }}">Awaiting review</a>
                <a href="{{ route('tp.workflow.index', ['filter' => 'scheduled']) }}" class="{{ $filter === 'scheduled' ? 'tp-button-primary' : 'tp-button-secondary' }}">Scheduled</a>
            </div>
        </div>

        @if ($items->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No workflow items match the current filter.</div>
        @else
            <div class="tp-metabox__body">
                <div class="tp-table-wrap">
                    <table class="tp-table tp-table--responsive tp-table--sticky-head">
                        <thead class="tp-table__thead">
                            <tr>
                                <th class="tp-table__th">Item</th>
                                <th class="tp-table__th">State</th>
                                <th class="tp-table__th">Owner</th>
                                <th class="tp-table__th">Reviewer</th>
                                <th class="tp-table__th">Approver</th>
                                <th class="tp-table__th">Next action</th>
                                <th class="tp-table__th">Scheduled</th>
                                <th class="tp-table__th text-right">Open</th>
                            </tr>
                        </thead>
                        <tbody class="tp-table__tbody">
                            @foreach ($items as $item)
                                @php
                                    $resourceModel = $resources->resolveModel((string) $item->resource_type, (int) $item->resource_id);
                                    $resourceTitle = is_object($resourceModel) ? trim((string) ($resourceModel->title ?? '')) : '';
                                @endphp
                                <tr class="tp-table__row">
                                    <td class="tp-table__td" data-label="Item">
                                        <div class="font-semibold text-slate-900">
                                            {{ $resourceTitle !== '' ? $resourceTitle : $resources->labelFor((string) $item->resource_type).' #'.$item->resource_id }}
                                        </div>
                                        <div class="tp-muted text-xs">{{ $resources->labelFor((string) $item->resource_type) }} #{{ $item->resource_id }}</div>
                                        <div class="tp-muted text-xs">{{ $item->hasPendingRevision() ? 'Working copy staged' : 'Live item in sync' }}</div>
                                    </td>
                                    <td class="tp-table__td" data-label="State">
                                        <span class="tp-badge tp-badge-info">{{ str_replace('_', ' ', ucfirst((string) $item->editorial_state)) }}</span>
                                    </td>
                                    <td class="tp-table__td" data-label="Owner">{{ $item->owner?->name ?? '—' }}</td>
                                    <td class="tp-table__td" data-label="Reviewer">{{ $item->reviewer?->name ?? '—' }}</td>
                                    <td class="tp-table__td" data-label="Approver">{{ $item->approver?->name ?? '—' }}</td>
                                    <td class="tp-table__td" data-label="Next action">{{ $item->nextActionLabel() }}</td>
                                    <td class="tp-table__td" data-label="Scheduled">{{ $item->scheduled_publish_at?->diffForHumans() ?? '—' }}</td>
                                    <td class="tp-table__td text-right" data-label="Open">
                                        <a href="{{ $resources->editUrl((string) $item->resource_type, (int) $item->resource_id) }}" class="tp-button-link">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tp-metabox__body">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
