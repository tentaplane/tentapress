# Static Deploy

Static site generation for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/static-deploy` |
| Version | 0.6.0 |
| Provider | `TentaPress\StaticDeploy\StaticDeployServiceProvider` |

## Features

- Generate static HTML for all pages and posts
- Include theme assets (CSS/JS)
- Generate sitemap.xml and robots.txt
- Generate 404.html
- Download as ZIP archive
- Run saved find/replace rules on staged export files before zipping
- Review and download stored export archives from the admin screen
- Use a balanced two-column admin layout on larger screens
- Present stored exports in a simpler, scan-friendly history list

## Dependencies

None.

## Admin Menu

| Label | Route | Capability | Position | Parent |
|-------|-------|------------|----------|--------|
| Static Deploy | `tp.static.index` | `deploy_static` | 100 | Settings |

## Output

The generated ZIP contains:
- Pre-rendered HTML files
- Theme assets
- `sitemap.xml`
- `robots.txt`
- `404.html`

## Replacement Rules

Static Deploy can persist reusable find/replace rules in the admin UI and apply them to the staged export right before the ZIP archive is created.

- Rules are stored as JSON in plugin settings.
- Each rule requires `find` and `replace` values.
- Optional `files` glob patterns limit which exported files are touched.
- If `files` is omitted, Static Deploy targets text-like files such as `*.html`, `*.xml`, `*.txt`, `*.css`, `*.js`, and `*.json`.
- The admin screen includes quick actions to load a working example payload or reset the saved rules to `[]`.

Example:

```json
[
    {
        "find": "<html",
        "replace": "<html data-static-export=\"1\"",
        "files": ["*.html"]
    },
    {
        "find": "https://example.com",
        "replace": "https://cdn.example.com",
        "files": ["*.html", "sitemap.xml"]
    }
]
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/static-deploy
```

**Note:** This is an optional plugin. Not enabled by default.
