@extends('tentapress-admin::layouts.shell')

@section('title', $taxonomy->label . ' Terms')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $taxonomy->label }}</h1>
            <p class="tp-description">
                Manage {{ strtolower($taxonomy->label) }} for this taxonomy.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.taxonomies.index') }}" class="tp-button-secondary">Back to taxonomies</a>
            <a href="{{ route('tp.taxonomies.terms.create', ['taxonomy' => $taxonomy->id]) }}" class="tp-button-primary">Create term</a>
        </div>
    </div>

    <div class="tp-metabox">
        @if ($terms->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No terms exist for this taxonomy yet.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Term</th>
                            <th class="tp-table__th">Parent</th>
                            <th class="tp-table__th">Assignments</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($terms as $term)
                            <tr>
                                <td class="tp-table__td">
                                    <div class="font-medium text-black">{{ $term->name }}</div>
                                    <div class="text-sm text-black/60">
                                        <code class="tp-code">{{ $term->slug }}</code>
                                        @if ($term->description)
                                            <span class="ml-2">{{ $term->description }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="tp-table__td">
                                    {{ $taxonomy->is_hierarchical ? ($term->parent?->name ?? 'None') : 'Not used' }}
                                </td>
                                <td class="tp-table__td">{{ $term->assignments_count }}</td>
                                <td class="tp-table__td text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('tp.taxonomies.terms.edit', ['taxonomy' => $taxonomy->id, 'term' => $term->id]) }}" class="tp-button-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('tp.taxonomies.terms.destroy', ['taxonomy' => $taxonomy->id, 'term' => $term->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-button-secondary">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
