# Import

Content import functionality for TentaPress.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/import`                       |
| Version  | 0.1.8                                     |
| Provider | `TentaPress\Import\ImportServiceProvider` |

## Features

- Import site content from JSON export
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
