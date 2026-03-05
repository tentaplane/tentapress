<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;

final class UpdateTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        /** @var TpTaxonomy|null $taxonomy */
        $taxonomy = $this->route('taxonomy');
        /** @var TpTerm|null $term */
        $term = $this->route('term');
        $taxonomyId = (int) ($taxonomy?->id ?? 0);

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_terms', 'slug')->where('taxonomy_id', $taxonomyId)->ignore((int) ($term?->id ?? 0))],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                'integer',
                'different:term',
                Rule::exists('tp_terms', 'id')->where('taxonomy_id', $taxonomyId),
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter a term name.',
            'name.max' => 'Term names may not be longer than 255 characters.',
            'slug.regex' => 'Slugs may only contain lowercase letters, numbers, and dashes.',
            'slug.unique' => 'That slug is already in use for this taxonomy.',
            'description.max' => 'Descriptions may not be longer than 2000 characters.',
            'parent_id.different' => 'A term cannot be its own parent.',
            'parent_id.exists' => 'Choose a parent term from the same taxonomy.',
        ];
    }
}
