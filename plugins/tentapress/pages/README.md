# Pages

Page management for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/pages` |
| Version | 0.1.3 |
| Provider | `TentaPress\Pages\PagesServiceProvider` |

## Features

- Create, edit, and delete pages
- Draft and published states
- Theme layout selection
- Block-based content editor
- Full-screen editing mode
- SEO fields integration
- Public rendering by slug

## Dependencies

None.

## Database

| Table | Purpose |
|-------|---------|
| `tp_pages` | Page records |

## Admin Menu

| Label | Route | Capability | Icon | Position |
|-------|-------|------------|------|----------|
| Pages | `tp.pages.index` | `manage_pages` | file | 20 |

## Public Routes

| Route | Description |
|-------|-------------|
| `/{slug}` | Render page by slug |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/pages
```
