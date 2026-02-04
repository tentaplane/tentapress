# Themes

Theme management UI for TentaPress.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/themes`                       |
| Version  | 0.1.4                                     |
| Provider | `TentaPress\Themes\ThemesServiceProvider` |

## Features

- List available themes
- Activate/deactivate themes
- View theme details (layouts, menu locations)
- Theme screenshots

## Dependencies

None.

## Admin Menu

| Label  | Route             | Capability      | Icon       | Position | Parent   |
|--------|-------------------|-----------------|------------|----------|----------|
| Themes | `tp.themes.index` | `manage_themes` | paintbrush | 50       | Settings |

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/themes
```
