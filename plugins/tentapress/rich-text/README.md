# Rich Text

Adds a rich-text block for free-form HTML content in the Blocks editor.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/rich-text` |
| Version | 0.1.0 |
| Provider | `TentaPress\RichText\RichTextServiceProvider` |

## Features

- Rich text editor block with HTML output
- Optional plugin so installations can disable HTML blocks

## Dependencies

- tentapress/blocks

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/rich-text
```
