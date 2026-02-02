# Media

Upload and manage media files for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/media` |
| Version | 0.1.2 |
| Provider | `TentaPress\Media\MediaServiceProvider` |

## Features

- Upload images and files
- Media library browser
- Alt text and captions
- Thumbnail generation
- Media selector for pages, posts, blocks, SEO

## Dependencies

- `tentapress/users`

## Database

| Table | Purpose |
|-------|---------|
| `tp_media` | Media file records |

## Admin Menu

| Label | Route | Capability | Icon | Position |
|-------|-------|------------|------|----------|
| Media | `tp.media.index` | `manage_media` | image | 35 |

## Configuration

Media URL driver configured in `config/tentapress.php`:

```php
'media' => [
    'url_driver' => env('TP_MEDIA_URL_DRIVER', 'local'),
],
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/media
```
