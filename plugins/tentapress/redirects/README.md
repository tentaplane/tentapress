# Redirects

First-party redirect management for TentaPress.

## Plugin Details

| Field    | Value                                           |
|----------|-------------------------------------------------|
| ID       | `tentapress/redirects`                          |
| Version  | 0.3.0                                           |
| Provider | `TentaPress\Redirects\RedirectsServiceProvider` |

## Goal

Provide safe, operator-managed redirect governance so slug changes and migrations do not break public URLs.

## Features

- Manual redirect CRUD in admin (`/admin/redirects`)
- Bulk enable/disable actions from redirect index
- Runtime web middleware for active 301/302 redirects
- Conflict checks against owned static routes
- Loop prevention checks for self/chained redirects
- Slug-change auto redirect generation for pages and posts
- In-form diagnostics preview endpoint for collision/loop checks
- Import mapping report ingestion command for migration redirects
- Redirect lifecycle audit event records
- Policy toggle for auto-applying slug-change redirects
- Suggestion queue with approve/reject workflow
- Import conflict staging into pending suggestions

## Admin Menu

| Label     | Route                | Capability   | Parent   |
|-----------|----------------------|--------------|----------|
| Redirects | `tp.redirects.index` | `manage_seo` | Settings |

## Commands

```bash
php artisan tp:redirects:import-mappings storage/app/tp-import-reports/<token>.json
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/redirects
composer test:filter -- Redirects
```

## Rollout Checklist

1. Run `php artisan tp:plugins sync`.
2. Enable plugin: `php artisan tp:plugins enable tentapress/redirects`.
3. Run migrations: `php artisan migrate`.
4. Validate admin routes:
   - `/admin/redirects`
   - `/admin/redirects/suggestions`
   - `/admin/redirects/settings`
5. Validate runtime redirects with sample 301 and 302 records.
6. Run regression tests: `composer test:filter -- Redirects`.

## Rollback Guidance

1. Disable plugin: `php artisan tp:plugins disable tentapress/redirects --force`.
2. Confirm redirect middleware no longer applies (`/legacy-url` should no longer redirect).
3. If needed, restore previous database backup for `tp_redirects`, `tp_redirect_events`, and `tp_redirect_suggestions`.
