# Admin Shell

Admin UI shell providing the base layout, navigation, and shared components for TentaPress admin screens.

## Plugin Details

| Field    | Value                                             |
|----------|---------------------------------------------------|
| ID       | `tentapress/admin-shell`                          |
| Version  | 0.6.16                                            |
| Provider | `TentaPress\AdminShell\AdminShellServiceProvider` |

## Features

- Base admin layout (header, sidebar, content area)
- Parent-only navigation groups expand inline instead of navigating to placeholder screens
- Shared admin navigation groups can declare explicit ordering metadata, including the built-in `Structure` group
- Mobile/tablet responsive behavior for sidebar, topbar, and dashboard widgets
- Shared responsive admin table pattern (`tp-table--responsive`) for plugin index/list screens
- Accessibility improvements for dialogs, toast announcements, responsive tables, and mobile navigation controls
- Navigation menu built dynamically from plugin manifests
- Shared UI components and `tp-*` CSS utility classes
- Notification/toast system
- Confirmation dialogs via `data-confirm` (replaces native alert/confirm)
- Admin asset entrypoints (CSS/JS)

## Dependencies

None.

## Admin Routes

| Route    | Name           | Description     |
|----------|----------------|-----------------|
| `/admin` | `tp.dashboard` | Admin dashboard |

## Assets

### Goal

Ship admin assets as part of the plugin so a clean install can load the admin UI without requiring Node/Bun.

### Source and build

- Admin assets live in this plugin:
    - `resources/css/admin.css` - Tailwind CSS with `tp-*` utilities
    - `resources/js/admin.js` - Alpine.js components
- Plugin `vite.config.js` builds into plugin-local `build/` and writes a Vite `manifest.json`.
- Admin assets use stable filenames (`admin.js`, `admin-styles.css`) so repeated builds do not force semver bumps for hash-only changes.
- Cache busting is handled at runtime by appending a content hash query string to generated asset URLs.
- `tentapress.json` declares the admin asset entry keys (`admin`, `admin-styles`).
- Admin layout loads assets through plugin directives:
    - `@tpPluginStyles('tentapress/admin-shell')`
    - `@tpPluginScripts('tentapress/admin-shell')`
- Themes continue compiling their own assets within theme folders.

### Versioning policy

- Bump the plugin version when admin-shell source changes in `src/`, `resources/`, routes, or plugin metadata.
- Do not bump the version for a rebuild alone when the compiled asset content is unchanged.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/admin-shell

# Run admin-shell baseline endpoint tests
composer test:filter -- AdminDashboardAccessTest

# Run admin-shell guard/integration edge-case tests
composer test:filter -- AdminDashboardGuardEdgeCaseTest
```
