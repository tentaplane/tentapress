# Page Editor

Notion-style continuous page editor for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/page-editor` |
| Version | 0.2.0 |
| Provider | `TentaPress\\PageEditor\\PageEditorServiceProvider` |

## Features

- Continuous writing surface (Editor.js based)
- Slash command inserter
- Inline formatting tools
- Header, list, quote, delimiter, image, code, checklist, callout, and embed blocks
- Full-screen editor mode for pages and posts
- Per-entry editor choice (`blocks` or `page`)
- JSON document storage + renderer integration

## Dependencies

- `tentapress/pages`
- `tentapress/posts`

## Data Model Integration

This plugin stores page-editor documents in the `content` JSON column used by pages/posts when `editor_driver = page`.

## Asset Build

Plugin assets are built inside the plugin package:

```bash
cd plugins/tentapress/page-editor
bun install
bun run build
```

If Bun is not available:

```bash
npm install
npm run build
```

Build output:

- `plugins/tentapress/page-editor/build`

When the plugin is enabled, assets are published to:

- `public/plugins/tentapress/page-editor/build`

When the plugin is disabled, published assets are removed.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/page-editor
```
