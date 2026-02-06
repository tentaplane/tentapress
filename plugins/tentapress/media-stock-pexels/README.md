# Media Stock (Pexels)

Pexels provider plugin for the TentaPress Media stock library.

## Plugin Details

| Field    | Value                                                      |
|----------|------------------------------------------------------------|
| ID       | `tentapress/media-stock-pexels`                             |
| Version  | 0.1.0                                                      |
| Provider | `TentaPress\\MediaStockPexels\\MediaStockPexelsServiceProvider` |

## Dependencies

- `tentapress/media`
- `tentapress/settings`

## Configuration

Set the Pexels API Key in Media → Stock Library → Settings.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/media-stock-pexels
```
