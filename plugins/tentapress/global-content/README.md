# Global Content

Reusable synced sections and template parts for TentaPress.

## Plugin Details

| Field    | Value                                                        |
|----------|--------------------------------------------------------------|
| ID       | `tentapress/global-content`                                  |
| Version  | 0.1.7                                                        |
| Provider | `TentaPress\\GlobalContent\\GlobalContentServiceProvider`    |

## Purpose

Provide a central library of reusable sections and template parts that can be referenced across pages, posts, page editor content, builder layouts, and themes.

## Features

- Central CRUD for reusable global content entries
- Synced reference block for blocks and builder
- Synced page-editor tool for global content references
- Visual Builder authoring in full-screen mode only
- Explicit detach-to-local-copy flow in blocks and builder
- Builder summaries prefer the global content title for reference blocks
- Theme rendering helper for published template parts via `@tpGlobalContent($slug)`
- Usage indexing for page and post references
- Save-time and render-time recursion protection

## Admin Menu

| Label          | Parent      | Route                     | Capability              | Icon | Position |
|----------------|-------------|---------------------------|-------------------------|------|----------|
| Global Content | `Structure` | `tp.global-content.index` | `manage_global_content` | copy | 85       |

## Dependencies

- `tentapress/admin-shell`
- `tentapress/blocks`
- `tentapress/builder`
- `tentapress/page-editor`
- `tentapress/pages`
- `tentapress/posts`
- `tentapress/users`

## Configuration

This plugin is intentionally self-contained. It does not require `.env` variables or root `config/tentapress.php` changes for normal operation.

## Dependency Behavior

- The plugin relies on `tentapress/blocks`, `tentapress/builder`, and `tentapress/page-editor` for its authoring and consumption surfaces.
- If the plugin is disabled, its admin routes, menu entries, reference rendering, and theme directive disappear cleanly.
- Existing stored references fail safe when the plugin is disabled because the shared renderers no longer resolve the plugin-owned reference types.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/global-content
./vendor/bin/pint --dirty
composer test:filter -- GlobalContent
```
