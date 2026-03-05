@extends('tentapress-admin::layouts.shell')

@section('title', 'Taxonomies')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Taxonomies</h1>
            <p class="tp-description">Browse the registered taxonomies and manage their terms.</p>
        </div>
    </div>

    <div class="tp-metabox">
        @if ($taxonomies->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No taxonomies are registered yet.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Taxonomy</th>
                            <th class="tp-table__th">Type</th>
                            <th class="tp-table__th">Terms</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($taxonomies as $taxonomy)
                            <tr>
                                <td class="tp-table__td">
                                    <div class="font-medium text-black">{{ $taxonomy->label }}</div>
                                    <div class="text-sm text-black/60">
                                        <code class="tp-code">{{ $taxonomy->key }}</code>
                                        @if ($taxonomy->description)
                                            <span class="ml-2">{{ $taxonomy->description }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="tp-table__td">
                                    {{ $taxonomy->is_hierarchical ? 'Hierarchical' : 'Flat' }}
                                </td>
                                <td class="tp-table__td">{{ $taxonomy->terms_count }}</td>
                                <td class="tp-table__td text-right">
                                    <a href="{{ route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id]) }}" class="tp-button-secondary">
                                        Manage terms
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
