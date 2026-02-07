# Admin Shell

Admin UI shell providing the base layout, navigation, and shared components for TentaPress admin screens.

## Plugin Details

| Field    | Value                                             |
|----------|---------------------------------------------------|
| ID       | `tentapress/admin-shell`                          |
| Version  | 0.6.0                                             |
| Provider | `TentaPress\AdminShell\AdminShellServiceProvider` |

## Features

- Base admin layout (header, sidebar, content area)
- Navigation menu built dynamically from plugin manifests
- Shared UI components and `tp-*` CSS utility classes
- Notification/toast system
- Confirmation dialogs via `data-confirm` (replaces native alert/confirm)
- Admin asset entrypoints (CSS/JS)

## Dependencies

None.

## Admin Routes

| Route    | Name                 | Description     |
|----------|----------------------|-----------------|
| `/admin` | `tp.admin.dashboard` | Admin dashboard |

## Assets

### Goal

Ship admin assets as part of the plugin so a clean install can load the admin UI without requiring Node/Bun.

### Source and build

- Admin assets live in this plugin:
    - `resources/css/admin.css` - Tailwind CSS with `tp-*` utilities
    - `resources/js/admin.js` - Alpine.js components
- Plugin `vite.config.js` builds into plugin-local `build/` and writes a Vite `manifest.json`.
- `tentapress.json` declares the admin asset entry keys (`admin`, `admin-styles`).
- Admin layout loads assets through plugin directives:
    - `@tpPluginStyles('tentapress/admin-shell')`
    - `@tpPluginScripts('tentapress/admin-shell')`
- Themes continue compiling their own assets within theme folders.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/admin-shell
```
