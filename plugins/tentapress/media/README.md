# Media

Upload and manage media files for TentaPress.

## Plugin Details

| Field    | Value                                   |
|----------|-----------------------------------------|
| ID       | `tentapress/media`                      |
| Version  | 0.3.0                                   |
| Provider | `TentaPress\Media\MediaServiceProvider` |

## Goal

Provide a first-party media library for uploads, management, and reuse across Pages/Posts/Blocks/SEO.

## Scope (v1)

- Upload + browse media assets (images + generic files).
- Stock library import (via provider plugins).
- Attribution metadata stored with assets.
- Basic metadata: title, alt text, caption, mime type, size, dimensions.
- Simple library with search (no folders).
- Permissions via `manage_media` capability.

## Features

- Upload images and files
- Media library browser + stock library search
- Alt text and captions
- Stock library import + attribution (provider plugins)
- Media selector for pages, posts, blocks, SEO

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

- Media index: grid + list with previews, type, size, and actions.
- Upload screen: file upload + metadata fields.
- Edit screen: metadata editing + details panel.
- Stock library: search and import from external providers (provider plugins).

## Storage

- Uses Laravel filesystem; default disk `public`.
- No generated variants yet (original file only).

## Integrations (current)

- Blocks: image + gallery blocks use the media selector.
- Posts/Pages: featured image selection via media picker.
- SEO: OG image selector uses the media library.

## Open questions

- Do we want image variants/sizes in v1.1?
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
```
