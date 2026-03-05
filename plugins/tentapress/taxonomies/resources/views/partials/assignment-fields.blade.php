@php
    $formId = is_string($formId ?? null) && $formId !== '' ? $formId : 'post-form';
    $fieldsets = is_array($fieldsets ?? null) ? $fieldsets : [];
    $selectedByTaxonomy = is_array($selectedByTaxonomy ?? null) ? $selectedByTaxonomy : [];
    $oldAssignments = old('taxonomy_terms');
@endphp

<div class="tp-metabox">
    <div class="tp-metabox__title">Taxonomies</div>
    <div class="tp-metabox__body space-y-4">
        @if ($fieldsets === [])
            <div class="tp-muted text-sm">No taxonomies are available yet.</div>
        @else
            @foreach ($fieldsets as $fieldset)
                @php
                    $taxonomy = $fieldset['taxonomy'] ?? null;
                    if (! $taxonomy instanceof \TentaPress\Taxonomies\Models\TpTaxonomy) {
                        continue;
                    }

                    $terms = $fieldset['terms'] ?? [];
                    $terms = is_array($terms) ? $terms : [];
                    $taxonomyId = (int) $taxonomy->id;
                    $selected = is_array($oldAssignments) && isset($oldAssignments[$taxonomyId])
                        ? (array) $oldAssignments[$taxonomyId]
                        : ($selectedByTaxonomy[$taxonomyId] ?? []);
                    $selected = array_map(static fn (mixed $value): int => (int) $value, $selected);
                    $selected = array_values(array_unique(array_filter($selected, static fn (int $value): bool => $value > 0)));
                    $errorKey = 'taxonomy_terms.'.$taxonomyId;
                @endphp

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="tp-label mb-0">{{ $taxonomy->label }}</div>
                        @if ($taxonomy->is_hierarchical)
                            <span class="tp-muted text-xs">Hierarchical</span>
                        @endif
                    </div>

                    @if (trim((string) $taxonomy->description) !== '')
                        <div class="tp-help">{{ $taxonomy->description }}</div>
                    @endif

                    @if ($terms === [])
                        <div class="tp-muted text-xs">No terms available.</div>
                    @else
                        <div class="max-h-52 space-y-2 overflow-auto rounded-lg border border-slate-200 bg-white px-3 py-2">
                            @foreach ($terms as $term)
                                @if (! $term instanceof \TentaPress\Taxonomies\Models\TpTerm)
                                    @continue
                                @endif

                                @php
                                    $termId = (int) $term->id;
                                @endphp

                                <label class="flex items-start gap-2">
                                    <input
                                        type="checkbox"
                                        name="taxonomy_terms[{{ $taxonomyId }}][]"
                                        form="{{ $formId }}"
                                        value="{{ $termId }}"
                                        class="tp-checkbox mt-0.5"
                                        @checked(in_array($termId, $selected, true)) />
                                    <span class="text-sm leading-5 text-slate-700">{{ $term->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @error($errorKey)
                        <div class="tp-help text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        @endif
    </div>
</div>
