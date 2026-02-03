# Blocks Naming Conventions (Actions/Links)

**Status:** Adopted
**Last updated:** 2026-02-03

## Purpose
Standardize action/link naming across blocks so the editor, renderer, and theme overrides remain consistent and
predictable.

## Canonical schema

### Actions
Use a single schema for buttons/links in every block:

```json
{
  "actions": [
    { "label": "Get started", "url": "/signup", "style": "primary" },
    { "label": "View docs", "url": "/docs", "style": "outline" }
  ]
}
```

- **actions**: array of action objects, ordered by priority.
- **action object** fields:
  - `label` (string, required)
  - `url` (string, required)
  - `style` (string, optional) — `primary|outline|ghost|link`
  - `target` (string, optional) — `_blank` supported if needed

### Singular link
If a block only needs a single link (e.g., Image link), use:

```json
{ "link": { "url": "https://...", "label": "Optional label" } }
```

## Field naming rules

- Use nouns for objects: `actions`, `link`, `media`, `items`.
- Use `url`, not `link_url`, and `label`, not `button_label`.
- Keep alignment/size/background fields unchanged unless there’s a strong reason to rename.

## Block mappings (current)

### hero
- Uses `actions[]` (replaces `primary_cta.*` and `secondary_cta.*`).

### cta
- Uses `actions[]` (replaces `button.*` and `secondary_button.*`).

### buttons
- Uses `actions[]` as the normalized array.

### newsletter
- Uses `actions[]` (single item) or `link` if only a link is needed.

### image
- Uses `link.url` (+ optional `link.label`).

## Editor behavior

- Editor displays block-specific UI but serializes to `actions[]` and `link`.
- When loading legacy data, map to `actions[]`/`link` in memory and persist on save.

## Follow-ups

- Decide if `rel` is needed on actions in addition to `target`.
- Confirm whether `style` tokens should be global or theme-specific.
