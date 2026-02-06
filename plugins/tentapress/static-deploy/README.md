# Static Deploy

Static site generation for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/static-deploy` |
| Version | 0.1.4 |
| Provider | `TentaPress\StaticDeploy\StaticDeployServiceProvider` |

## Features

- Generate static HTML for all pages and posts
- Include theme assets (CSS/JS)
- Generate sitemap.xml and robots.txt
- Generate 404.html
- Download as ZIP archive

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

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/static-deploy
```

**Note:** This is an optional plugin. Not enabled by default.
