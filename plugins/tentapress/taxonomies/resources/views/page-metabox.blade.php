@php
    $show = in_array(($mode ?? 'edit'), ['create', 'edit'], true);
@endphp

@if ($show)
    @php
        $manager = app(\TentaPress\Taxonomies\Support\TermAssignmentManager::class);
        $pageId = (int) ($page->id ?? 0);
    @endphp

    @include('tentapress-taxonomies::partials.assignment-fields', [
        'formId' => 'page-form',
        'fieldsets' => $manager->assignmentFieldsets(),
        'selectedByTaxonomy' => $manager->selectedTermIds(\TentaPress\Pages\Models\TpPage::class, $pageId),
    ])
@endif
