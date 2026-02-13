# Import

Content import functionality for TentaPress.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/import`                       |
| Version  | 0.15.0                                    |
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
- Stream real-time import progress in the review screen (pages/posts/media item-by-item with running counters)
- Emit phase-level stream updates (start/completed for pages, posts, media) for clearer status messaging
- Handles pages, posts, media references, settings

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
