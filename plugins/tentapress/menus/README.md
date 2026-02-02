# Menus

Navigation menu management for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/menus` |
| Version | 0.1.2 |
| Provider | `TentaPress\Menus\MenusServiceProvider` |

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

## Database

| Table | Purpose |
|-------|---------|
| `tp_menus` | Menu definitions |
| `tp_menu_items` | Menu item records |

## Admin Menu

| Label | Route | Capability | Icon | Position |
|-------|-------|------------|------|----------|
| Menus | `tp.menus.index` | `manage_menus` | menu | 40 |

## Theme Integration

Themes define menu locations in `tentapress.json`:

```json
"menu_locations": {
    "primary": "Primary Navigation",
    "footer": "Footer Navigation"
}
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/menus
```
