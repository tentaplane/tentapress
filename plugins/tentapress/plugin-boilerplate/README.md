# Plugin Boilerplate

Cloneable first-party plugin starter for TentaPress.

## Plugin Details

| Field | Value |
|------|-------|
| ID | `tentapress/plugin-boilerplate` |
| Version | `0.1.0` |
| Provider | `TentaPress\\PluginBoilerplate\\PluginBoilerplateServiceProvider` |

## Purpose

Provide a small, explicit starting point for new TentaPress plugins without introducing abstractions too early.

## Included Patterns

- Composer and `tentapress.json` manifests aligned to the current plugin architecture
- One service provider responsible for registration and bootstrapping
- Single-action admin controllers
- Dedicated form request with validation messages
- Explicit settings service backed by `tentapress/settings`
- Capability seeding during application boot
- Console command registration behind `runningInConsole()`
- Feature tests with local plugin autoloading

## What To Rename After Cloning

- Package name in `composer.json`
- Plugin ID, label, menu route, and version in `tentapress.json`
- Namespace `TentaPress\\PluginBoilerplate`
- Route URI and route names
- Capability key `manage_plugin_boilerplate`
- Settings keys under `plugin_boilerplate.*`
- View namespace `tentapress-plugin-boilerplate`
- Command signature `tp:plugin-boilerplate:check`

## Suggested Next Steps

- Add migrations only when the plugin genuinely owns data
- Add public `web.php` or `api.php` routes only when needed
- Add frontend assets only when the plugin owns UI beyond Blade and admin shell utilities
- Keep services small and explicit - avoid helper layers until a second use case appears

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/plugin-boilerplate
./vendor/bin/pint --dirty
composer test:filter -- PluginBoilerplate
```
