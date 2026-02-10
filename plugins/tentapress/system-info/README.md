# System Info

Diagnostics and plugin management for TentaPress.

## Plugin Details

| Field    | Value                                             |
| -------- | ------------------------------------------------- |
| ID       | `tentapress/system-info`                          |
| Version  | 0.5.1                                             |
| Provider | `TentaPress\SystemInfo\SystemInfoServiceProvider` |

## Features

- Environment information (PHP, Laravel, app versions)
- Database driver and status
- Storage status
- Cache status
- Plugin list with enable/disable controls
- Queue-based plugin installs from admin (`vendor/package`, GitHub URL, or Packagist URL)
- Queue-based plugin updates from admin (defaults to installed plugins; full `composer update` gated by `TP_ALLOW_FULL_COMPOSER_UPDATE=true`)
- Serialized install jobs to avoid overlapping Composer runs
- Runtime cache refresh for OPCache-backed hosts after plugin lifecycle changes

## Dependencies

None.

## Admin Menu

| Label       | Route              | Capability         | Icon | Position | Parent   |
| ----------- | ------------------ | ------------------ | ---- | -------- | -------- |
| System Info | `tp.system-info`   | `view_system_info` | info | 95       | -        |
| Plugins     | `tp.plugins.index` | `manage_plugins`   | plug | 40       | Settings |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/system-info
```
