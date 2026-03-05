# Taxonomies

Standalone taxonomy registration and persistence for TentaPress.

## Plugin Details

| Field    | Value                                    |
| -------- | ---------------------------------------- |
| ID       | `tentapress/taxonomies`                  |
| Version  | 0.4.0                                    |
| Provider | `TentaPress\Taxonomies\TaxonomiesServiceProvider` |

## Features

- Registers built-in `category` and `tag` taxonomies
- Provides a plugin-owned taxonomy registry for future custom taxonomies
- Persists taxonomies, terms, and polymorphic term assignments
- Supports hierarchical and flat taxonomy definitions
- Syncs registered taxonomy definitions into the database at boot once migrations are available
- Includes admin taxonomy browsing and term CRUD screens
- Prevents deleting terms that still have child terms or content assignments
- Adds reusable taxonomy assignment controls for post/page editing flows
- Validates and persists assigned terms during content create/update operations
- Adds taxonomy term filters to posts/pages admin index screens

## Dependencies

- `tentapress/system`

## Database

| Table                 | Purpose                               |
| --------------------- | ------------------------------------- |
| `tp_taxonomies`       | Taxonomy definitions and config       |
| `tp_terms`            | Taxonomy terms and parent hierarchy   |
| `tp_term_assignments` | Polymorphic content-to-term mapping   |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/taxonomies
composer test
composer test:filter -- TaxonomiesBaselineFlowTest
composer test:filter -- TaxonomiesAdminFlowTest
composer test:filter -- TaxonomiesContentAssignmentFlowTest
composer test:filter -- TaxonomiesAdminFilteringFlowTest
```
