# Media Stock (Unsplash)

Unsplash provider plugin for the TentaPress Media stock library.

## Plugin Details

| Field    | Value                                                        |
|----------|--------------------------------------------------------------|
| ID       | `tentapress/media-stock-unsplash`                             |
| Version  | 0.1.0                                                        |
| Provider | `TentaPress\\MediaStockUnsplash\\MediaStockUnsplashServiceProvider` |

## Dependencies

- `tentapress/media`
- `tentapress/settings`

## Configuration

Set the Unsplash Access Key in Media → Stock Library → Settings.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/media-stock-unsplash
```
