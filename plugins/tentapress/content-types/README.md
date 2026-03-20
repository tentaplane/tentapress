# Content Types

Admin-defined publishable content types for TentaPress.

## Plugin Details

| Field | Value |
|------|-------|
| ID | `tentapress/content-types` |
| Version | `0.1.0` |
| Provider | `TentaPress\\ContentTypes\\ContentTypesServiceProvider` |

## Purpose

Model structured content beyond pages and posts, such as case studies, team members, services, events, and locations, using a first-party TentaPress plugin.

## Features

- Admin-managed content type builder with plugin-owned persistence
- Per-type base paths for archive and single routes
- Publishable entries with blocks-based body authoring
- Custom field support for text, textarea, number, boolean, date/time, select, and relation fields
- Plugin-owned JSON API with per-type visibility controls
- Theme-aware single rendering with plugin fallbacks

## Admin Menu

| Label | Parent | Route | Capability |
|------|--------|-------|------------|
| Content Types | `Structure` | `tp.content-types.index` | `manage_content_types` |

## Configuration

This plugin is intentionally self-contained. It does not require root config changes or `.env` values for normal use.

## Dependency Behaviour

- Requires `tentapress/admin-shell`, `tentapress/blocks`, and `tentapress/system`
- If disabled, the plugin’s routes, admin UI, and runtime behaviour disappear cleanly
- `headless-api`, `seo`, `workflow`, and `search` are not required for this first release

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/content-types
php artisan migrate --force
./vendor/bin/pint --dirty
composer test:filter -- ContentTypes
```
