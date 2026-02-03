# Block - Markdown Editor

Provides a Markdown editor block for the TentaPress blocks system.

## Plugin Details

| Field    | Value                                                               |
|----------|---------------------------------------------------------------------|
| ID       | `tentapress/block-markdown-editor`                                  |
| Version  | 0.1.2                                                               |
| Provider | `TentaPress\BlockMarkdownEditor\BlockMarkdownEditorServiceProvider` |

## Requirements

- `tentapress/blocks`

## Installation

Enable the plugin:

```bash
php artisan tp:plugins enable tentapress/block-markdown-editor
```

## Block

- **Type**: `blocks/markdown`
- **View**: `blocks.markdown`

## Notes

- The block stores Markdown content in its `content` prop.
- Rendering is handled by the registered block view.
