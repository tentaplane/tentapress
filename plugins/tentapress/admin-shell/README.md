# Admin Shell

Admin UI shell providing the base layout, navigation, and shared components for TentaPress admin screens.

## Plugin Details

| Field    | Value                                             |
|----------|---------------------------------------------------|
| ID       | `tentapress/admin-shell`                          |
| Version  | 0.2.9                                             |
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

Keep admin CSS/JS owned by the `admin-shell` plugin while compiling them with a plugin-local Vite config.

### Source and build

- Admin assets live in this plugin:
    - `resources/css/admin.css` - Tailwind CSS with `tp-*` utilities
    - `resources/js/admin.js` - Alpine.js components
- Plugin `vite.config.js` compiles admin assets + a public fallback stylesheet.
- Admin layouts reference plugin assets via `@vite([...])`.
- Themes compile their own assets within theme folders (theme assets are excluded from the root build).

Built by the admin-shell Vite config, output to `public/build/`.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/admin-shell
```
