# Revisions

Revision history for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                           |
| -------- | ----------------------------------------------- |
| ID       | `tentapress/revisions`                          |
| Version  | 0.1.0                                           |
| Provider | `TentaPress\Revisions\RevisionsServiceProvider` |

## Features

- Captures snapshots when pages and posts are created or updated
- Deduplicates unchanged saves to avoid noisy history
- Shows the latest revision history in page and post editors
- Stores editor driver, status, layout, blocks, and page-doc payloads

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
```
