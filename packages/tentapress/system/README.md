# TentaPress System

Core platform layer for TentaPress plugin and theme management.

## Package Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| Name     | `tentapress/system`                       |
| Version  | 0.3.16                                    |
| Provider | `TentaPress\System\SystemServiceProvider` |

## Overview

The system package provides the foundational infrastructure for TentaPress:

- Plugin discovery and lifecycle management
- Theme discovery and activation
- Admin middleware stack
- Console commands for plugin/theme operations

## Components

### Plugin System

| Class            | Purpose                                        |
|------------------|------------------------------------------------|
| `PluginRegistry` | Discover and track plugins from filesystem     |
| `PluginManager`  | Enable, disable, and boot plugins              |
| `PluginManifest` | Parse and validate `tentapress.json` manifests |

### Theme System

| Class           | Purpose                                      |
|-----------------|----------------------------------------------|
| `ThemeRegistry` | Discover and track themes from filesystem    |
| `ThemeManager`  | Activate themes and register view namespaces |
| `ThemeManifest` | Parse and validate theme manifests           |

### Middleware

| Class                       | Purpose                          |
|-----------------------------|----------------------------------|
| `AdminMiddleware`           | Composite admin middleware stack |
| `AdminAuthMiddleware`       | Admin authentication             |
| `CanMiddleware`             | Capability/permission checks     |
| `AdminErrorPagesMiddleware` | Admin-styled error pages         |

### Support

| Class         | Purpose                    |
|---------------|----------------------------|
| `AdminRoutes` | Route registration helpers |
| `JsonPayload` | JSON encoding helper       |
| `Paths`       | Path resolution utilities  |

## Console Commands

### Plugin Commands

```bash
php artisan tp:plugins sync        # Discover and cache plugins
php artisan tp:plugins list        # List all plugins with status
php artisan tp:plugins enable <id> # Enable a plugin
php artisan tp:plugins disable <id> # Disable a plugin
php artisan tp:plugins defaults    # Enable default plugins
php artisan tp:plugins cache       # Rebuild plugin cache
php artisan tp:plugins clear-cache # Clear plugin cache
```

Plugin commands no longer run migrations automatically. Run `php artisan migrate` explicitly after plugin installs or
upgrades.
Plugin lifecycle/cache actions also clear compiled views so Blade/Blaze output stays coherent after plugin state
changes.

### Theme Commands

```bash
php artisan tp:themes sync         # Discover themes
php artisan tp:themes list         # List all themes
php artisan tp:themes activate <id> # Activate a theme
```

## Database

| Table        | Purpose                       |
|--------------|-------------------------------|
| `tp_plugins` | Plugin enabled/disabled state |
| `tp_themes`  | Active theme record           |

## Cache

| File                             | Purpose                        |
|----------------------------------|--------------------------------|
| `bootstrap/cache/tp_plugins.php` | Enabled plugins and boot order |
| `bootstrap/cache/tp_theme.php`   | Active theme metadata          |

For OPCache-backed hosts, runtime cache refresh helpers invalidate these files after plugin/theme lifecycle actions.

## Blaze Integration

Blaze is integrated as an opt-in optimization layer for anonymous components.

- Toggle with `TP_BLAZE_ENABLED=true|false` (default `false`).
- Debug toggle: `TP_BLAZE_DEBUG=true|false` (default `false`).
- The active theme's `views/components` path is resolved dynamically at runtime.
- Configure active theme strategy in `config/tentapress.php` under `blaze.active_theme_components`.
- Configure additional optimized directories (for plugin/package components) under `blaze.paths`.
- Keep `fold` disabled unless a component is fully static and safe for compile-time folding.
- Laravel exception renderer views are excluded from Blaze compilation for compatibility.
- System config overrides raw `BLAZE_*` env keys so TentaPress controls Blaze behavior consistently.
- After changing Blaze path configuration, clear compiled views:

```bash
php artisan view:clear
```

## Discovery

Plugins and themes are discovered by scanning for `tentapress.json` manifest files:

- Plugins: `plugins/**/tentapress.json`
- Themes: `themes/**/tentapress.json`
- Vendor: `vendor/{namespaces}/**/tentapress.json`

Vendor namespaces are configured in `config/tentapress.php`.

## Integration

This package auto-registers via Laravel package discovery. The `SystemServiceProvider`:

1. Registers plugin and theme registries
2. Loads enabled plugins from cache
3. Boots plugin service providers
4. Registers admin routes and middleware

## Testing

This package keeps feature tests locally under `packages/tentapress/system/tests/Feature`.

```bash
composer test:filter -- PluginsListCommandTest
composer test:filter -- PluginsSyncCommandTest
composer test:filter -- PluginsFailurePathsCommandTest
```

Migration behavior for tests is provided by root Pest config via `RefreshDatabase`, so package migrations are applied
for each test run.
