# Import

Content import functionality for TentaPress.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/import`                       |
| Version  | 0.20.0                                    |
| Provider | `TentaPress\Import\ImportServiceProvider` |

## Features

- Import site content from TentaPress JSON export bundles
- Analyze and import WordPress WXR (`.xml`) exports (pages, posts, media metadata)
- Report unsupported WXR entities before import
- Show unsupported WXR sample entries and actionable XML parse errors
- Show source-to-destination URL mapping preview for redirect planning
- Accept common WXR XML MIME types during upload validation
- Use WXR-aware review controls (settings import options hidden for WXR files)
- Report featured image reference counts and attachment-resolution coverage
- Include WXR context and migration summary lines in completion notices
- Apply actor-based author/creator fallback for imported records where supported
- Write a persistent URL mapping report file for WXR runs
- Copy WXR attachment URLs into local media storage (`media/imports/wordpress/...`) when reachable
- Route users from review to a dedicated import progress screen for cleaner execution flow
- Emit phase-level stream updates (start/completed for pages, posts, media) for clearer status messaging
- Include per-entity skipped counters in run completion summaries and phase completion events
- Skip duplicate WXR source rows on repeated create-only runs (prevents `-2` slug duplication on reruns)
- Verify persisted URL mapping report artifacts through feature tests
- Report created/skipped/failed counts per entity in completion summaries and streamed phase updates
- Keep analyzed import tokens available for reruns/retries (no immediate token cleanup after first run)
- Refresh local media variants for imported media records and report refreshed counts
- Return user-facing admin notices for run-time token errors in non-stream import flow
- Handles pages, posts, media references, settings

## Operations

- Staging/operator runbook and release checklist:
  - `/docs/wordpress-importer`

## Dependencies

None.

## Admin Menu

| Label  | Route             | Capability    | Parent   |
|--------|-------------------|---------------|----------|
| Import | `tp.import.index` | `import_site` | Settings |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/import
```

**Note:** This is an optional plugin. Not enabled by default.
