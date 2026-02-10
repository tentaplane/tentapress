# Headless API

Headless REST API for TentaPress content delivery.

## Plugin Details

| Field    | Value                                           |
|----------|-------------------------------------------------|
| ID       | `tentapress/headless-api`                       |
| Version  | 0.1.0                                           |
| Provider | `TentaPress\\HeadlessApi\\HeadlessApiServiceProvider` |

## Features

- Versioned REST API under `/api/v1`
- Public read endpoints for site, pages, posts, menus, and media
- Published-content filtering for public endpoints
- SEO payload inclusion for pages and posts

## Endpoints (v1)

- `GET /api/v1/site`
- `GET /api/v1/pages`
- `GET /api/v1/pages/{slug}`
- `GET /api/v1/posts`
- `GET /api/v1/posts/{slug}`
- `GET /api/v1/menus/{location}`
- `GET /api/v1/media/{id}`

## Dependencies

- `tentapress/pages`
- `tentapress/posts`
- `tentapress/media`
- `tentapress/menus`
- `tentapress/settings`
- `tentapress/seo`

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/headless-api
```

**Note:** This is an optional plugin. Not enabled by default.
