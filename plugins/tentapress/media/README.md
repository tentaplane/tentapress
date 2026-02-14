# Media

Upload and manage media files for TentaPress.

## Plugin Details

| Field    | Value                                   |
|----------|-----------------------------------------|
| ID       | `tentapress/media`                      |
| Version  | 0.9.9                                   |
| Provider | `TentaPress\Media\MediaServiceProvider` |

## Goal

Provide a first-party media library for uploads, management, and reuse across Pages/Posts/Blocks/SEO.

## Scope (v1)

- Upload + browse media assets (images + generic files).
- Stock library import (via enabled provider plugins).
- Attribution metadata stored with assets.
- Basic metadata: title, alt text, caption, mime type, size, dimensions.
- Simple library with search (no folders).
- Permissions via `manage_media` capability.

## Features

- Upload images and files
- Media library browser + stock library search
- Alt text and captions
- Stock library import + attribution (provider plugins)
- Async stock imports with multi-select bulk add
- Imported stock items are marked in-place without inline notices
- Saved media view preference now applies before render to avoid list/grid flash
- Pagination controls in both list and grid media views (top + bottom) with clearer disabled/active states
- Edit media screen uses a true 50/50 desktop split with details first and actions directly under the form
- Edit media details now list built variants with preview links and rebuild buttons
- Local image ingest clamp (`2048x2048`) with resize-to-original
- Generated local image variants (`thumb`, `medium`, `large`) with preview-first admin rendering
- Media maintenance commands for optimize/backfill/verification workflows
- Optimization status badges in media index and details
- Media selector for pages, posts, blocks, SEO
- Frontend media reference resolver (ID/URL) for variant-aware image delivery

## Dependencies

- `tentapress/users`

## Data model

- `tp_media`
    - `id`
    - `title` (nullable)
    - `alt_text` (nullable)
    - `caption` (nullable)
    - `disk` (default `public`)
    - `path` (unique)
    - `original_name` (nullable)
    - `mime_type` (nullable)
    - `size` (nullable)
    - `width`, `height` (nullable)
    - `source_width`, `source_height` (nullable pre-clamp dimensions)
    - `variants` (nullable JSON derivative metadata)
    - `preview_variant` (nullable)
    - `optimization_status` (nullable)
    - `optimization_error` (nullable)
    - `source` (nullable)
    - `source_item_id` (nullable)
    - `source_url` (nullable)
    - `license` (nullable)
    - `license_url` (nullable)
    - `attribution` (nullable)
    - `attribution_html` (nullable)
    - `stock_meta` (nullable)
    - `created_by`, `updated_by`
    - timestamps

## Admin Menu

| Label | Route            | Capability     | Icon  | Position |
|-------|------------------|----------------|-------|----------|
| Media | `tp.media.index` | `manage_media` | image | 35       |

## Admin UI (current)

- Media index: grid + list with previews, type, size, actions, and styled pagination links at top and bottom.
- Upload screen: file upload + metadata fields.
- Edit screen: 50/50 desktop split with details on the left and metadata form + actions on the right.
- Edit details panel: built variants table with per-variant and rebuild-all actions.
- Stock library: available only when at least one stock provider plugin is enabled in plugin state.
- Optimizations: available only when at least one optimization provider plugin is enabled in plugin state.
- Active optimization service selector: only enabled optimization plugins are listed.

## Storage

- Uses Laravel filesystem; default disk `public`.
- Oversized image uploads are clamped to `2048x2048` and stored as canonical original.
- Local image variants are generated for preview/delivery defaults:
    - `thumb` (320w)
    - `medium` (768w)
    - `large` (1600w)

## Integrations (current)

- Blocks: image + gallery blocks use the media selector.
- Posts/Pages: featured image selection via media picker.
- SEO: OG image selector uses the media library.

## Open questions

- Should we add folders/collections later?
- Should uploads be restricted by role or file type?

## Configuration

Media URL driver configured in `config/tentapress.php`:

```php
'media' => [
    'url_driver' => env('TP_MEDIA_URL_DRIVER', 'local'),
],
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/media
composer test
composer test:filter -- MediaBaselineFlowTest
```

## Commands

```bash
php artisan tp:media:optimise --limit=200
php artisan tp:media:backfill-variants --limit=200
php artisan tp:media:verify-variants --limit=500
```
