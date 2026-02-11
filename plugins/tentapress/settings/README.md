# Settings

Site settings management for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/settings` |
| Version | 0.1.5 |
| Provider | `TentaPress\Settings\SettingsServiceProvider` |

## Features

- Site name and tagline
- Homepage and blog page settings
- General site configuration

## Dependencies

None.

## Database

| Table | Purpose |
|-------|---------|
| `tp_settings` | Key-value settings storage |

## Admin Menu

| Label | Route | Capability | Position |
|-------|-------|------------|----------|
| Settings | `tp.settings.index` | `manage_settings` | 90 |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/settings
composer test
composer test:filter -- SettingsAdminFlowTest
composer test:filter -- SettingsEdgeCaseTest
```
