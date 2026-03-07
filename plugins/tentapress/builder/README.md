# Builder

Visual drag-and-drop builder for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                      |
|----------|--------------------------------------------|
| ID       | `tentapress/builder`                       |
| Version  | 0.6.13                                     |
| Provider | `TentaPress\\Builder\\BuilderServiceProvider` |

## Features

- Visual block canvas for pages and posts
- Drag/drop block ordering
- Inspector controls for block fields
- Essential presentation controls via `props.presentation`
- Live theme preview via non-iframe server document fragments
- Global content preview support for full-screen builder flows, including active theme assets
- Global content reference blocks show the referenced title in builder summaries
- Preview-safe block rendering for third-party embeds (thumbnail/placeholder instead of live iframe execution)
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

## Third-Party Theme Preview Contract

Builder preview renders using the active theme's normal layout view resolution. No preview-specific layout files are required.

### Migration Guide (Legacy Theme -> Contract Views)

1. Keep output compatible with CSS-only preview:
   - include the same structural chrome (header/content/footer),
   - do not depend on runtime JS behavior for editor fidelity.
2. Validate block marker compatibility:
   - builder injects `data-tp-builder-block-index` wrappers in preview output;
   - ensure these wrappers are not stripped by layout logic.
3. Verify fallback parity:
   - compare contract view output against standard public layout output for representative pages/posts.
4. Rollout guidance:
   - use fragment mode by default (`tp.builder.preview_mode=fragment`);
   - keep iframe fallback only as temporary rollback if explicitly enabled in environments where needed.

### Verification Checklist

- Preview loads with no iframe dependency.
- Standard theme layouts render correctly for each supported layout key.
- Selected block highlighting/click mapping works in preview.
- No public front-end rendering regressions for pages/posts.

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
