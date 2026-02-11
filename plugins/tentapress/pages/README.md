# Pages

Page management for TentaPress.

## Plugin Details

| Field    | Value                                   |
| -------- | --------------------------------------- |
| ID       | `tentapress/pages`                      |
| Version  | 0.3.2                                   |
| Provider | `TentaPress\Pages\PagesServiceProvider` |

## Features

- Create, edit, and delete pages
- Draft and published states
- Theme layout selection
- Block-based content editor
- Full-screen editing mode
- Media picker payloads now include media IDs for variant-aware image rendering
- SEO fields integration
- Public rendering by slug

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
```
