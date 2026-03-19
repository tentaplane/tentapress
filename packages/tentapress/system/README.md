# TentaPress System

Core platform layer for TentaPress plugin and theme management.

## Package Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| Name     | `tentapress/system`                       |
| Version  | 0.5.0                                     |
| Provider | `TentaPress\System\SystemServiceProvider` |

## Overview

The system package provides the foundational infrastructure for TentaPress:

- Plugin discovery and lifecycle management
- Theme discovery and activation
- Editor driver registry for pluggable page/post editing experiences
- Admin middleware stack
- Console commands for plugin/theme operations
- Boilerplate plugin generation from Packagist or local fallback

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
| `EditorDriverRegistry` | Register/resolve page and post editor drivers |
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
First-party pre-1.0 install guidance uses an explicit Composer constraint so admin/manual installs resolve packages that
are only available as `0.x-dev`.

### Boilerplate Plugin Generator

```bash
php artisan tp:plugin:make
php artisan tp:plugin:make acme events-schedule "Events Schedule"
php artisan tp:plugin:make acme events-schedule "Events Schedule" --namespace="Acme\\EventsSchedule"
php artisan tp:plugin:make acme events-schedule "Events Schedule" --enable
```

The generator creates a new plugin by copying the published boilerplate template, then rewriting the package id,
namespace, class names, route names, capability key, settings prefix, view namespace, and human-facing labels.

Default source behaviour is `--source=auto`:

- Try Packagist first using `tentapress/boilerplate`
- Fall back to the local repository copy at `plugins/tentapress/boilerplate` if the package is unavailable

Supported options:

```bash
php artisan tp:plugin:make <vendor> <slug> "<name>" \
  --source=auto|packagist|local \
  --template-package=tentapress/boilerplate \
  --template-version=<version> \
  --namespace="Vendor\\PluginName" \
  --description="Describe what the plugin does." \
  --enable
```

Use `--source=packagist` to require a downloadable template package and fail if it cannot be fetched.
Use `--source=local` when working on the monorepo template itself or when offline.
After generation the command syncs the plugin registry, rebuilds plugin cache, and clears compiled views.

### Theme Commands

```bash
php artisan tp:themes sync         # Discover themes
php artisan tp:themes list         # List all themes
php artisan tp:themes activate <id> # Activate a theme
```

### Catalog Commands

```bash
php artisan tp:catalog generate    # Regenerate docs/catalog/first-party-plugins.json
php artisan tp:catalog check       # Fail if the committed catalog is stale
```

The first-party plugin catalog is generated from `plugins/tentapress/*/tentapress.json` manifests and preserves
catalog-only metadata already present in the committed JSON feed.

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
composer test:filter -- BoilerplateMakeCommandTest
```

Migration behavior for tests is provided by root Pest config via `RefreshDatabase`, so package migrations are applied
for each test run.
