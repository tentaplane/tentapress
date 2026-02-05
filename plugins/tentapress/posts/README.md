# Posts

Blog post management for TentaPress.

## Plugin Details

| Field    | Value                                   |
|----------|-----------------------------------------|
| ID       | `tentapress/posts`                      |
| Version  | 0.2.0                                   |
| Provider | `TentaPress\Posts\PostsServiceProvider` |

## Features

- Create, edit, and delete blog posts
- Draft, scheduled, and published states
- Author assignment
- Featured image
- Block-based content editor
- Full-screen editing mode
- SEO fields integration

## Dependencies

- `tentapress/users`
- `tentapress/blocks`

## Database

| Table      | Purpose      |
|------------|--------------|
| `tp_posts` | Post records |

## Admin Menu

| Label | Route            | Capability     | Icon      | Position |
|-------|------------------|----------------|-----------|----------|
| Posts | `tp.posts.index` | `manage_posts` | file-text | 30       |

## Public Routes

| Route          | Description |
|----------------|-------------|
| `/blog`        | Blog index  |
| `/blog/{slug}` | Single post |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/posts
```
