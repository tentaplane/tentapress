# Headless API

Headless REST API for TentaPress content delivery.

## Plugin Details

| Field    | Value                                           |
|----------|-------------------------------------------------|
| ID       | `tentapress/headless-api`                       |
| Version  | 0.2.0                                           |
| Provider | `TentaPress\\HeadlessApi\\HeadlessApiServiceProvider` |

## Features

- Versioned REST API under `/api/v1`
- Public read endpoints for site, pages, posts, menus, and media
- Public taxonomy endpoints for taxonomy/term discovery
- Published-content filtering for public endpoints
- SEO payload inclusion for pages and posts
- Taxonomy term serialization in post payloads

## Endpoints (v1)

- `GET /api/v1/site`
- `GET /api/v1/pages`
- `GET /api/v1/pages/{slug}`
- `GET /api/v1/posts`
- `GET /api/v1/posts/{slug}`
- `GET /api/v1/taxonomies`
- `GET /api/v1/taxonomies/{taxonomy}/terms`
- `GET /api/v1/menus/{location}`
- `GET /api/v1/media/{id}`

### Query Parameters

- `GET /api/v1/pages`
  - `per_page` (default `12`, min `1`, max `100`)
  - `slug` (exact slug filter)
  - `layout` (exact layout filter)
- `GET /api/v1/posts`
  - `per_page` (default `12`, min `1`, max `100`)
  - `author` (numeric author id)
  - `q` (searches title and slug)
  - `taxonomy` (taxonomy key filter)
  - `term` (term slug filter, optionally combined with `taxonomy`)

### Response Examples

#### Success: `GET /api/v1/posts/{slug}`

```json
{
    "data": {
        "id": 42,
        "type": "post",
        "title": "Hello API",
        "slug": "hello-api",
        "status": "published",
        "layout": "post",
        "editor_driver": "blocks",
        "published_at": "2026-02-27T10:00:00+00:00",
        "permalink": "/blog/hello-api",
        "author": {
            "id": 1,
            "name": "Headless Author"
        },
        "content_raw": {
            "editor_driver": "blocks",
            "blocks": [],
            "content": null
        },
        "content_html": "",
        "seo": {
            "title": "Post SEO",
            "description": "Post description",
            "canonical_url": null,
            "robots": null,
            "og_title": null,
            "og_description": null,
            "og_image": null,
            "twitter_title": null,
            "twitter_description": null,
            "twitter_image": null
        },
        "updated_at": "2026-02-27T10:05:00+00:00"
    }
}
```

#### Not Found: `GET /api/v1/posts/{slug}`

```json
{
    "error": {
        "code": "not_found",
        "message": "Post not found"
    }
}
```

#### Index Envelope: `GET /api/v1/pages`

```json
{
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 12,
        "total": 0,
        "last_page": 1
    }
}
```

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
