# System Info

Diagnostics and plugin management for TentaPress.

## Plugin Details

| Field    | Value                                             |
| -------- | ------------------------------------------------- |
| ID       | `tentapress/system-info`                          |
| Version  | 0.8.4                                             |
| Provider | `TentaPress\SystemInfo\SystemInfoServiceProvider` |

## Features

- Environment information (PHP, Laravel, app versions)
- Database driver and status
- Storage status
- Cache status
- Plugin list with enable/disable controls
- Plugin catalog for discovering first-party plugins (`tentapress/*`) with card-grid visual browsing
- Catalog icon metadata support from local/hosted catalog sources
- Install queue progress feedback on catalog install actions
- Queue-based plugin installs from admin (`vendor/package`, GitHub URL, or Packagist URL)
- Queue-based plugin updates from admin (defaults to installed plugins; full `composer update` gated by `TP_ALLOW_FULL_COMPOSER_UPDATE=true`)
- Install/update jobs auto-detect usable php and composer binaries; config overrides are optional
- Serialized install jobs to avoid overlapping Composer runs
- Runtime cache refresh for OPCache-backed hosts after plugin lifecycle changes

## Dependencies

None.

## Admin Menu

| Label       | Route              | Capability         | Icon | Position | Parent   |
| ----------- | ------------------ | ------------------ | ---- | -------- | -------- |
| System Info | `tp.system-info`   | `view_system_info` | info | 95       | -        |
| Plugin Catalogue | `tp.plugins.catalog` | `view_system_info` | plug | 10       | System Info |
| Plugins     | `tp.plugins.index` | `manage_plugins`   | plug | 40       | Settings |

## Configuration

```php
'catalog' => [
    'local_path' => 'docs/catalog/first-party-plugins.json',
    'url' => 'https://github.com/tentaplane/tentapress/blob/main/docs/catalog/first-party-plugins.json',
    'timeout_seconds' => 5,
    'cache_ttl_seconds' => 900,
    'require_https' => true,
],

'plugin_lifecycle' => [
    'php_binary' => '',
    'composer_binary' => '',
],
```

- `local_path` points to the generated first-party catalog feed in this monorepo.
- `url` defaults to the TentaPress repository source-of-truth feed and is resolved to raw GitHub content when fetching.
- If hosted data is unavailable, the catalog falls back to local data (and cached hosted data when available).
- Catalog plugin entries can include an optional `icon` field.
- `plugin_lifecycle` overrides are optional and only needed when automatic binary detection is not sufficient.
- Regenerate the local feed with `php artisan tp:catalog generate`.
- Validate the committed feed with `php artisan tp:catalog check`.

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
