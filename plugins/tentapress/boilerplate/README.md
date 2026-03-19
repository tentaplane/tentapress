# Boilerplate

Cloneable first-party plugin starter for TentaPress.

## Plugin Details

| Field | Value |
|------|-------|
| ID | `tentapress/boilerplate` |
| Version | `0.1.0` |
| Provider | `TentaPress\\Boilerplate\\BoilerplateServiceProvider` |

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
- Namespace `TentaPress\\Boilerplate`
- Route URI and route names
- Capability key `manage_boilerplate`
- Settings keys under `boilerplate.*`
- View namespace `tentapress-boilerplate`
- Command signature `tp:boilerplate:check`

## Suggested Next Steps

- Add migrations only when the plugin genuinely owns data
- Add public `web.php` or `api.php` routes only when needed
- Add frontend assets only when the plugin owns UI beyond Blade and admin shell utilities
- Keep services small and explicit - avoid helper layers until a second use case appears

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/boilerplate
./vendor/bin/pint --dirty
composer test:filter -- Boilerplate
```
