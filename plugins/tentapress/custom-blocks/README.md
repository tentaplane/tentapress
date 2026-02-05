# Custom Blocks

Single-file block discovery for active TentaPress themes.

## Plugin Details

| Field | Value |
|---|---|
| ID | `tentapress/custom-blocks` |
| Version | `0.1.0` |
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

## Commands

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/custom-blocks
php artisan tp:plugins cache
```
