# Pages

Page management for TentaPress.

## Plugin Details

| Field    | Value                                   |
| -------- | --------------------------------------- |
| ID       | `tentapress/pages`                      |
| Version  | 0.6.1                                   |
| Provider | `TentaPress\Pages\PagesServiceProvider` |

## Features

- Create, edit, and delete pages
- Draft and published states
- Theme layout selection
- Block-based content editor
- Pluggable editor driver selection (`blocks`, `page`, `builder`)
- Full-screen editing mode
- Media picker payloads now include media IDs for variant-aware image rendering
- SEO fields integration
- Taxonomy metabox only renders when `tentapress/taxonomies` is enabled
- Revision history, autosave draft reload, compare, and restore integration when `tentapress/revisions` is enabled
- Workflow approval, assignment, and revision-backed working copy integration when `tentapress/workflow` is enabled
- Public rendering by slug
- Optional content reference source registration for cross-plugin linking

## Dependencies

None.

## Database

| Table      | Purpose      |
| ---------- | ------------ |
| `tp_pages` | Page records |

## Admin Menu

| Label | Route            | Capability     | Icon | Position |
| ----- | ---------------- | -------------- | ---- | -------- |
| Pages | `tp.pages.index` | `manage_pages` | file | 20       |

## Public Routes

| Route     | Description         |
| --------- | ------------------- |
| `/{slug}` | Render page by slug |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/pages
```

## Testing

This plugin keeps feature tests locally under `plugins/tentapress/pages/tests/Feature`.

```bash
composer test
composer test:filter -- HomeRedirectTest
composer test:filter -- PublishedPageRenderingTest
composer test:filter -- PageSlugStatusFallbackEdgeCaseTest
```
