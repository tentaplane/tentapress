# Blocks

Block registry and rendering system for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/blocks`                       |
| Version  | 0.2.12                                    |
| Provider | `TentaPress\Blocks\BlocksServiceProvider` |

## Features

- Block registry (type → schema + renderer)
- Block validation on save
- Full-screen block editor
- Block list view with inline editing
- Right-side details panel
- Block inserter controls
- Page outline navigation
- Field types: text, textarea, richtext, select, image, repeater, link, actions

## Dependencies

None.

## Available Blocks

- Hero
- Heading
- Rich Text
- Image
- Gallery
- CTA / Button group
- Columns
- FAQ (repeater)
- Embed
- Divider / Spacer

## Extending

Themes and plugins can register additional blocks via the block registry.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/blocks
```
