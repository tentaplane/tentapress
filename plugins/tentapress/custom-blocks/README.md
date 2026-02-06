# Custom Blocks

Single-file block discovery for active TentaPress themes.

## Plugin Details

| Field    | Value                                                   |
|----------|---------------------------------------------------------|
| ID       | `tentapress/custom-blocks`                              |
| Version  | `0.1.1`                                                 |
| Provider | `TentaPress\\CustomBlocks\\CustomBlocksServiceProvider` |

## What It Does

- Scans the active theme at `views/blocks/**/*.blade.php`
- Registers only files that include valid block metadata in a `tp:block` Blade comment
- Registers definitions into the `BlockRegistry`
- Makes discovered blocks available in the existing blocks editor

## Block File Format

Create a block file in your active theme, for example:

`themes/<vendor>/<theme>/views/blocks/pricing.blade.php`

```blade
{{-- tp:block
{
    "name": "Pricing",
    "description": "Tiered pricing cards",
    "fields": [
        { "key": "headline", "label": "Headline", "type": "text" },
        { "key": "plans", "label": "Plans JSON", "type": "textarea", "rows": 8 }
    ],
    "defaults": {
        "headline": "Pricing",
        "plans": []
    }
}
--}}

<section>
  <h2>{{ $props['headline'] ?? '' }}</h2>
</section>
```

### Metadata behavior

- `tp:block` metadata is required for a file to be registered as a custom block.
- `type` is optional. If omitted, it defaults to `tentapress/custom-blocks/<file-slug>`.
- `view` is optional. If omitted, it defaults to `blocks.<relative.path>`.
- `name` and `description` fallback automatically if omitted.
- `fields`, `defaults`, `variants`, and `default_variant` follow the same shape as JSON block definitions.

## Field Types (Admin Inputs)

Each field is an object in the `fields` array with the core keys:

- `key` (string, required): stored under `props[key]`
- `label` (string, required): shown in the editor UI
- `type` (string, required): one of the types below

Optional keys supported on all field types:

- `help` (string): helper text below the input
- `placeholder` (string): placeholder text for text-like inputs

### Supported types

**Text**

```json
{ "key": "headline", "label": "Headline", "type": "text" }
```

**Textarea**

```json
{ "key": "body", "label": "Body", "type": "textarea", "rows": 4 }
```

**Markdown**

```json
{ "key": "content", "label": "Content", "type": "markdown", "rows": 10, "height": "240px" }
```

**Rich text**

```json
{ "key": "summary", "label": "Summary", "type": "richtext" }
```

**Select**

```json
{
  "key": "alignment",
  "label": "Alignment",
  "type": "select",
  "options": [
    { "value": "left", "label": "Left" },
    { "value": "center", "label": "Center" }
  ]
}
```

**Toggle**

```json
{ "key": "show_border", "label": "Show border", "type": "toggle", "toggle_label": "Enabled" }
```

**Number**

```json
{ "key": "columns", "label": "Columns", "type": "number", "min": 1, "max": 6, "step": 1 }
```

**Range**

```json
{ "key": "opacity", "label": "Opacity", "type": "range", "min": 0, "max": 100, "step": 5 }
```

**Color**

```json
{ "key": "accent", "label": "Accent Color", "type": "color" }
```

**URL**

```json
{ "key": "cta_url", "label": "CTA URL", "type": "url" }
```

**Media**

```json
{ "key": "hero_image", "label": "Hero Image", "type": "media" }
```

**Media list**

```json
{ "key": "gallery", "label": "Gallery", "type": "media-list" }
```

### Notes

- Any unrecognized `type` falls back to a standard text input.
- `defaults` are applied when fields are missing on a block.
- `variants` and `default_variant` are optional and follow the same structure as core block definitions.

## Commands

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/custom-blocks
php artisan tp:plugins cache
```
