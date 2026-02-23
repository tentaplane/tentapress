# Builder

Visual drag-and-drop builder for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                      |
|----------|--------------------------------------------|
| ID       | `tentapress/builder`                       |
| Version  | 0.1.0                                      |
| Provider | `TentaPress\\Builder\\BuilderServiceProvider` |

## Features

- Visual block canvas for pages and posts
- Drag/drop block ordering
- Inspector controls for block fields
- Essential presentation controls via `props.presentation`
- Live theme preview in iframe through secure snapshot tokens
- Built-in starter patterns
- Undo/redo and keyboard shortcuts

## Dependencies

- `tentapress/blocks`
- `tentapress/pages`
- `tentapress/posts`
- `tentapress/admin-shell`

## Data Model Integration

This plugin stores builder output in the `blocks` JSON column and uses `editor_driver = builder`.

## Asset Build

```bash
cd plugins/tentapress/builder
bun install
bun run build
```

If Bun is not available:

```bash
npm install
npm run build
```

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/builder
```
