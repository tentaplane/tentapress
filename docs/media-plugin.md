# Media plugin

## Goal
Provide a first-party media library for uploads, management, and reuse across Pages/Posts/Blocks/SEO.

## Scope (v1)
- Upload + browse media assets (images + generic files).
- Basic metadata: title, alt text, caption, mime type, size, dimensions.
- Simple library with search (no folders).
- Permissions via `manage_media` capability.

## Data model (current)
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
  - `created_by`, `updated_by`
  - timestamps

## Admin UI (current)
- Media index: searchable table with previews, type, size, and actions.
- Upload screen: file upload + metadata fields.
- Edit screen: metadata editing + details panel.

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
