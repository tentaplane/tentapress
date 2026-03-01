<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

final class BuiltinTaxonomies
{
    public static function register(TaxonomyRegistry $registry): void
    {
        if (! $registry->has('category')) {
            $registry->register(new TaxonomyDefinition(
                key: 'category',
                label: 'Categories',
                singularLabel: 'Category',
                description: 'Hierarchical content grouping for broad classification.',
                isHierarchical: true,
                config: [
                    'default_terms' => [],
                    'supports_multiple_terms' => true,
                ],
            ));
        }

        if (! $registry->has('tag')) {
            $registry->register(new TaxonomyDefinition(
                key: 'tag',
                label: 'Tags',
                singularLabel: 'Tag',
                description: 'Flat content grouping for flexible labeling.',
                config: [
                    'default_terms' => [],
                    'supports_multiple_terms' => true,
                ],
            ));
        }
    }
}
