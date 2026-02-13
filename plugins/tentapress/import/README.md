# Import

Content import functionality for TentaPress.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/import`                       |
| Version  | 0.5.0                                     |
| Provider | `TentaPress\Import\ImportServiceProvider` |

## Features

- Import site content from TentaPress JSON export bundles
- Analyze and import WordPress WXR (`.xml`) exports (pages, posts, media metadata)
- Report unsupported WXR entities before import
- Show unsupported WXR sample entries and actionable XML parse errors
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
