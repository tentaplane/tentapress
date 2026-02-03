# Admin Shell

Admin UI shell providing the base layout, navigation, and shared components for TentaPress admin screens.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/admin-shell` |
| Version | 0.1.4 |
| Provider | `TentaPress\AdminShell\AdminShellServiceProvider` |

## Features

- Base admin layout (header, sidebar, content area)
- Navigation menu built dynamically from plugin manifests
- Shared UI components and `tp-*` CSS utility classes
- Notification/toast system
- Admin asset entrypoints (CSS/JS)

## Dependencies

None.

## Admin Routes

| Route | Name | Description |
|-------|------|-------------|
| `/admin` | `tp.admin.dashboard` | Admin dashboard |

## Assets

Admin CSS and JS entrypoints live in this plugin:
- `resources/css/admin.css` - Tailwind CSS with `tp-*` utilities
- `resources/js/admin.js` - Alpine.js components

Built by root Vite config, output to `public/build/`.

More details: `docs/admin-assets.md`.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/admin-shell
```
