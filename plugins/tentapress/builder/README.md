# Builder

Visual drag-and-drop builder for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                      |
|----------|--------------------------------------------|
| ID       | `tentapress/builder`                       |
| Version  | 0.4.0                                      |
| Provider | `TentaPress\\Builder\\BuilderServiceProvider` |

## Features

- Visual block canvas for pages and posts
- Drag/drop block ordering
- Inspector controls for block fields
- Essential presentation controls via `props.presentation`
- Live theme preview via non-iframe server document fragments
- Built-in starter patterns
- Undo/redo and keyboard shortcuts

## Dependencies

- `tentapress/blocks`
- `tentapress/pages`
- `tentapress/posts`
- `tentapress/admin-shell`

## Data Model Integration

This plugin stores builder output in the `blocks` JSON column and uses `editor_driver = builder`.

## Preview Transport

Builder preview uses non-iframe server fragments via:

- `/admin/builder/snapshots/{token}/document` JSON transport

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
