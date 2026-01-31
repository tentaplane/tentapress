# Menu plugin

## Goal
Allow users to define navigation menus and map them to theme locations.

## Scope (v1)
- CRUD for menus and nested menu items.
- Assign menus to theme-defined locations.
- Render helper to output menus in themes.
- Permissions via `manage_menus` capability.

## Data model (current)
- `tp_menus`
  - `id`
  - `name`
  - `slug`
  - `created_by`, `updated_by`
  - timestamps
- `tp_menu_items`
  - `id`
  - `menu_id`
  - `parent_id` (nullable, for nesting)
  - `title`
  - `url`
  - `target` (nullable, e.g., `_blank`)
  - `sort_order`
  - `meta` (json)
  - timestamps
- `tp_menu_locations`
  - `id`
  - `location_key` (string)
  - `menu_id` (nullable)
  - timestamps

## Admin UI (current)
- Menus list + create/edit screens.
- Menu editor with manual ordering (move up/down) and parent selection.
- Location assignments sourced from theme manifest `menu_locations`.

## Theme integration (current)
- Theme manifest defines locations:
  - `menu_locations`: `{ "primary": "Primary Navigation" }`
- Views typically use:
  - `@php $primaryMenu = $tpMenus->itemsForLocation('primary') @endphp`
- `MenuRenderer::itemsForLocation()` returns nested arrays suitable for theme rendering.

## Routes (current)
- `/admin/menus` list, create, edit, update, delete.

## Open questions
- Do we want drag/drop nesting or async reordering in v1.1?
- Should we add auto-generated menus (e.g., pages list)?
