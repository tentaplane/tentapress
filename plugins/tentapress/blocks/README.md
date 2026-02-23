# Blocks

Block registry and rendering system for TentaPress pages and posts.

## Plugin Details

| Field    | Value                                     |
|----------|-------------------------------------------|
| ID       | `tentapress/blocks`                       |
| Version  | 0.10.0                                    |
| Provider | `TentaPress\Blocks\BlocksServiceProvider` |

## Features

- Block registry (type → schema + renderer)
- Block validation on save
- Full-screen block editor
- Block list view with inline editing
- Right-side details panel
- Block inserter controls
- Page outline navigation
- Field types: text, textarea, richtext, select, image, repeater, nested-blocks, link, actions
- Variant-aware image rendering for image-based blocks when media records are available

## Dependencies

None.

## Available Blocks

- Hero
- Heading
- Rich Text
- Image
- Gallery
- CTA / Button group
- Split Image + Content
- Split Layout (nested child blocks)
- FAQ (repeater)
- Embed
- Divider / Spacer

## Split Layout Child Payload

`blocks/split-layout` stores child blocks in:

- `props.left_blocks` (array of block objects)
- `props.right_blocks` (array of block objects)

Rules:

- Nesting depth is capped to one level.
- Child type `blocks/split-layout` is intentionally rejected to prevent recursive containers.
- Admin editor supports inline child controls for scalar field types and repeater rows.

## Extending

Themes and plugins can register additional blocks via the block registry.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/blocks
composer test
composer test:filter -- BlocksRenderingBaselineTest
composer test:filter -- BlocksEdgeCaseTest
```
