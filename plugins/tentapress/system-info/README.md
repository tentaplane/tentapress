# System Info

Diagnostics and plugin management for TentaPress.

## Plugin Details

| Field    | Value                                             |
| -------- | ------------------------------------------------- |
| ID       | `tentapress/system-info`                          |
| Version  | 0.7.0                                             |
| Provider | `TentaPress\SystemInfo\SystemInfoServiceProvider` |

## Features

- Environment information (PHP, Laravel, app versions)
- Database driver and status
- Storage status
- Cache status
- Plugin list with enable/disable controls
- Plugin catalog for discovering first-party plugins (`tentapress/*`) with card-grid visual browsing
- Catalog icon and preview metadata support from local/hosted catalog sources
- Install queue progress feedback on catalog install actions
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
| Plugin Catalogue | `tp.plugins.catalog` | `view_system_info` | plug | 39       | Settings |
| Plugins     | `tp.plugins.index` | `manage_plugins`   | plug | 40       | Settings |

## Configuration

```php
'catalog' => [
    'local_path' => 'docs/catalog/first-party-plugins.json',
    'url' => '',
    'timeout_seconds' => 5,
    'cache_ttl_seconds' => 900,
    'require_https' => true,
],
```

- `local_path` is the maintained source of truth in this monorepo for first-party catalog entries.
- `url` is optional. If set, hosted data overlays local entries by plugin id.
- If hosted data is unavailable, the catalog falls back to local data (and cached hosted data when available).
- Catalog plugin entries can include optional `icon` and `preview_image` fields.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/system-info
```

## Testing

```bash
composer test
composer test:filter -- SystemInfoDiagnosticsAccessTest
```
