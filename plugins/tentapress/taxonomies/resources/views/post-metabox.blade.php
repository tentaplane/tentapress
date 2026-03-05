@php
    $show = in_array(($mode ?? 'edit'), ['create', 'edit'], true);
@endphp

@if ($show)
    @php
        $manager = app(\TentaPress\Taxonomies\Support\TermAssignmentManager::class);
        $postId = (int) ($post->id ?? 0);
    @endphp

    @include('tentapress-taxonomies::partials.assignment-fields', [
        'formId' => 'post-form',
        'fieldsets' => $manager->assignmentFieldsets(),
        'selectedByTaxonomy' => $manager->selectedTermIds(\TentaPress\Posts\Models\TpPost::class, $postId),
    ])
@endif
