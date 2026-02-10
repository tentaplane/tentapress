# Menus

Navigation menu management for TentaPress.

## Plugin Details

| Field    | Value                                   |
| -------- | --------------------------------------- |
| ID       | `tentapress/menus`                      |
| Version  | 0.3.4                                   |
| Provider | `TentaPress\Menus\MenusServiceProvider` |

## Goal

Allow users to define navigation menus and map them to theme locations.

## Scope (v1)

- CRUD for menus and nested menu items.
- Assign menus to theme-defined locations.
- Render helper to output menus in themes.
- Permissions via `manage_menus` capability.

## Features

- Create and manage navigation menus
- Assign menus to theme locations
- Add pages, posts, and custom links
- Drag-and-drop ordering
- Nested menu items

## Dependencies

- `tentapress/users`
- `tentapress/pages`
- `tentapress/posts`
- `tentapress/settings`

## Data model

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

## Admin Menu

| Label | Route            | Capability     | Icon | Position |
| ----- | ---------------- | -------------- | ---- | -------- |
| Menus | `tp.menus.index` | `manage_menus` | menu | 40       |

## Admin UI (current)

- Menus list + create/edit screens.
- Menu editor with manual ordering (move up/down) and parent selection.
- Location assignments sourced from theme manifest `menu_locations`.

## Theme Integration

Themes define menu locations in `tentapress.json`:

```json
"menu_locations": {
    "primary": "Primary Navigation",
    "footer": "Footer Navigation"
}
```

## Routes (current)

- `/admin/menus` list, create, edit, update, delete.

## Open questions

- Do we want drag/drop nesting or async reordering in v1.1?
- Should we add auto-generated menus (e.g., pages list)?

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/menus
```
