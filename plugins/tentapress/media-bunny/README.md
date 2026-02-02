# Media Bunny CDN

Bunny CDN integration for TentaPress media URLs.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/media-bunny` |
| Version | 0.1.2 |
| Provider | `TentaPress\MediaBunny\MediaBunnyServiceProvider` |

## Features

- Generate media URLs via Bunny CDN
- Configurable CDN hostname

## Dependencies

- `tentapress/media`

## Configuration

Set the media URL driver to `bunny` and configure your CDN hostname:

```env
TP_MEDIA_URL_DRIVER=bunny
TP_BUNNY_CDN_HOSTNAME=your-zone.b-cdn.net
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/media-bunny
```

**Note:** This is an optional plugin. Not enabled by default.
