# SEO

SEO fields and metadata management for TentaPress.

## Plugin Details

| Field    | Value                               |
| -------- | ----------------------------------- |
| ID       | `tentapress/seo`                    |
| Version  | 0.2.7                               |
| Provider | `TentaPress\Seo\SeoServiceProvider` |

## Features

- Meta title and description per page/post
- OpenGraph fields (title, description, image)
- Twitter card fields
- Canonical URL support
- SEO field components for editors

## Dependencies

None.

## Admin Menu

| Label | Route          | Capability   | Position | Parent |
| ----- | -------------- | ------------ | -------- | ------ |
| SEO   | `tp.seo.index` | `manage_seo` | 20       | Pages  |

## Integration

SEO fields are integrated into page and post editors. Themes should render SEO meta tags in their layouts.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/seo
```
