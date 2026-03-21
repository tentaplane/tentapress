# Revisions

Revision history for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                           |
| -------- | ----------------------------------------------- |
| ID       | `tentapress/revisions`                          |
| Version  | 0.1.5                                           |
| Provider | `TentaPress\Revisions\RevisionsServiceProvider` |

## Features

- Captures snapshots when pages and posts are created or updated
- Deduplicates unchanged saves to avoid noisy history
- Persists autosave drafts and reloads them in editors on re-entry
- Shows revision history, compare flows, and restore actions in page and post editors
- Stores editor driver, status, layout, blocks, page-doc payloads, and restore metadata

## Dependencies

- `tentapress/pages`
- `tentapress/posts`

## Database

| Table          | Purpose                          |
| -------------- | -------------------------------- |
| `tp_revisions` | Snapshot history for content     |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/revisions
composer test
composer test:filter -- RevisionsBaselineFlowTest
composer test:filter -- HeadlessApiBaselineFlowTest
```
