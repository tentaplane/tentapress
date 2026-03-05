<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Models\TpTermAssignment;

final class TermAssignmentManager
{
    private const REQUEST_ATTRIBUTE_KEY = 'tentapress_taxonomy_assignments';

    /**
     * @return array<int,array{taxonomy:TpTaxonomy,terms:array<int,TpTerm>}>
     */
    public function assignmentFieldsets(): array
    {
        if (! Schema::hasTable('tp_taxonomies') || ! Schema::hasTable('tp_terms')) {
            return [];
        }

        $taxonomies = TpTaxonomy::query()->orderBy('label')->get();
        if ($taxonomies->isEmpty()) {
            return [];
        }

        $taxonomyIds = $taxonomies->pluck('id')->map(static fn (mixed $value): int => (int) $value)->all();
        $termGroups = TpTerm::query()
            ->whereIn('taxonomy_id', $taxonomyIds)
            ->orderBy('taxonomy_id')
            ->orderBy('name')
            ->get()
            ->groupBy('taxonomy_id');

        $fieldsets = [];
        foreach ($taxonomies as $taxonomy) {
            $terms = $termGroups->get($taxonomy->id);

            $fieldsets[] = [
                'taxonomy' => $taxonomy,
                'terms' => $terms !== null ? $terms->all() : [],
            ];
        }

        return $fieldsets;
    }

    /**
     * @return array<int,array<int>>
     */
    public function selectedTermIds(string $assignableType, int $assignableId): array
    {
        if ($assignableId <= 0 || ! Schema::hasTable('tp_term_assignments')) {
            return [];
        }

        /** @var array<int,array<int>> $selected */
        $selected = TpTermAssignment::query()
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableId)
            ->orderBy('taxonomy_id')
            ->orderBy('term_id')
            ->get(['taxonomy_id', 'term_id'])
            ->groupBy('taxonomy_id')
            ->map(
                static fn ($group): array => $group
                    ->pluck('term_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all()
            )
            ->all();

        return $selected;
    }

    public function validateAndRememberAssignments(Request $request): void
    {
        $request->attributes->set(self::REQUEST_ATTRIBUTE_KEY, $this->normalizeAssignmentsFromRequest($request));
    }

    public function syncRememberedAssignments(Request $request, string $assignableType, int $assignableId): void
    {
        if ($assignableId <= 0 || ! Schema::hasTable('tp_term_assignments')) {
            return;
        }

        $assignments = $request->attributes->get(self::REQUEST_ATTRIBUTE_KEY);
        if (! is_array($assignments)) {
            return;
        }

        $taxonomyIds = array_map(static fn (int|string $id): int => (int) $id, array_keys($assignments));
        if ($taxonomyIds === []) {
            return;
        }

        $connection = TpTermAssignment::query()->getModel()->getConnection();
        $now = now();

        $connection->transaction(function () use ($taxonomyIds, $assignableType, $assignableId, $assignments, $now): void {
            TpTermAssignment::query()
                ->where('assignable_type', $assignableType)
                ->where('assignable_id', $assignableId)
                ->whereIn('taxonomy_id', $taxonomyIds)
                ->delete();

            $rows = [];
            foreach ($assignments as $taxonomyId => $termIds) {
                foreach ($termIds as $termId) {
                    $rows[] = [
                        'taxonomy_id' => (int) $taxonomyId,
                        'term_id' => (int) $termId,
                        'assignable_type' => $assignableType,
                        'assignable_id' => $assignableId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($rows !== []) {
                TpTermAssignment::query()->insert($rows);
            }
        });
    }

    /**
     * @return array<int,array<int>>
     */
    private function normalizeAssignmentsFromRequest(Request $request): array
    {
        if (! Schema::hasTable('tp_taxonomies') || ! Schema::hasTable('tp_terms')) {
            return [];
        }

        $rawAssignments = $request->input('taxonomy_terms', []);
        if (! is_array($rawAssignments)) {
            throw ValidationException::withMessages([
                'taxonomy_terms' => 'Taxonomy assignments must be an array of terms.',
            ]);
        }

        $taxonomies = TpTaxonomy::query()->orderBy('id')->get()->keyBy('id');

        $normalized = [];
        foreach ($taxonomies as $taxonomy) {
            $normalized[(int) $taxonomy->id] = [];
        }

        if ($normalized === []) {
            return [];
        }

        $termsByTaxonomy = TpTerm::query()
            ->whereIn('taxonomy_id', array_keys($normalized))
            ->get(['id', 'taxonomy_id'])
            ->groupBy('taxonomy_id')
            ->map(
                static fn ($group): array => $group
                    ->pluck('id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all()
            )
            ->all();

        $errors = [];

        foreach ($rawAssignments as $taxonomyIdRaw => $termIdsRaw) {
            $taxonomyId = (int) $taxonomyIdRaw;
            $errorKey = 'taxonomy_terms.'.$taxonomyIdRaw;

            if (! $taxonomies->has($taxonomyId)) {
                $errors[$errorKey] = 'The selected taxonomy is invalid.';

                continue;
            }

            $selected = is_array($termIdsRaw) ? $termIdsRaw : [$termIdsRaw];

            $termIds = [];
            foreach ($selected as $termIdRaw) {
                $termId = (int) $termIdRaw;
                if ($termId > 0) {
                    $termIds[] = $termId;
                }
            }

            $termIds = array_values(array_unique($termIds));
            $validTermIds = $termsByTaxonomy[$taxonomyId] ?? [];

            foreach ($termIds as $termId) {
                if (! in_array($termId, $validTermIds, true)) {
                    $errors[$errorKey] = 'One or more selected terms are invalid for this taxonomy.';

                    continue 2;
                }
            }

            $config = is_array($taxonomies[$taxonomyId]->config) ? $taxonomies[$taxonomyId]->config : [];
            $supportsMultipleTerms = (bool) ($config['supports_multiple_terms'] ?? true);

            if (! $supportsMultipleTerms && count($termIds) > 1) {
                $errors[$errorKey] = 'Only one term can be selected for this taxonomy.';

                continue;
            }

            $normalized[$taxonomyId] = $termIds;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }
}
