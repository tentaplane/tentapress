# Tailwind Theme

A Tailwind CSS v4 theme for TentaPress.

## Theme Details

| Field         | Value                 |
|---------------|-----------------------|
| ID            | `tentapress/tailwind` |
| Version       | 0.2.2                 |
| CSS Framework | Tailwind CSS v4       |

## Layouts

| Key     | Label   | Description             |
|---------|---------|-------------------------|
| default | Default | Standard page layout    |
| landing | Landing | Full-width landing page |
| post    | Post    | Blog post layout        |

## Menu Locations

| Key     | Label              |
|---------|--------------------|
| primary | Primary Navigation |
| footer  | Footer Navigation  |

## Assets

This theme uses Tailwind CSS v4 with CSS-first configuration.

### Build

```bash
# Install dependencies
bun install --cwd themes/tentapress/tailwind

# Run dev server (HMR)
bun run --cwd themes/tentapress/tailwind dev

# Watch build (no HMR)
bun run --cwd themes/tentapress/tailwind watch

# Build assets
bun run --cwd themes/tentapress/tailwind build
```

### Entrypoints

- `resources/css/theme.css` - Tailwind import + custom styles
- `resources/js/theme.js` - Custom scripts

## Structure

```
tailwind/
├── composer.json       # Composer metadata
├── package.json        # NPM dependencies
├── vite.config.js      # Vite configuration
├── tentapress.json     # Theme manifest
├── screenshot.webp     # Theme preview
├── src/
│   └── TailwindThemeServiceProvider.php
├── views/
│   ├── layouts/
│   │   ├── default.blade.php
│   │   ├── landing.blade.php
│   │   └── post.blade.php
│   └── blocks/
│       └── (block overrides)
└── resources/
    ├── css/theme.css
    └── js/theme.js
```

## Tailwind Configuration

Tailwind v4 uses CSS-first configuration with `@theme`:

```css
@import "tailwindcss";

@theme {
	/* Custom theme variables */
}
```

## Customization

Override block views by creating `views/blocks/{block-key}.blade.php`.

## Cloning This Theme (Agent Checklist)

This section is a concrete checklist for duplicating the theme under a new vendor/name.

### 1) Copy the folder

```bash
cp -R themes/tentapress/tailwind themes/<vendor>/<theme>
```

Example:

```bash
cp -R themes/tentapress/tailwind themes/antigravity/neon-vibe
```

### 2) Update the theme manifest

Edit `themes/<vendor>/<theme>/tentapress.json`:

- `id`: `<vendor>/<theme>`
- `name`: human-friendly name
- `provider`: new PHP namespace + class
- `provider_path`: new provider PHP file (if renamed)
- `description`, `version`, `layouts`, `menu_locations`: adjust as needed

### 3) Update PHP namespaces + provider class

Update `themes/<vendor>/<theme>/src/*`:

- Rename namespace to match the new vendor/theme.
- Rename `TailwindThemeServiceProvider` to match the theme.
- Update class name in `tentapress.json` `provider`.

### 4) Update Composer autoload metadata

Edit `themes/<vendor>/<theme>/composer.json`:

- `name`: e.g. `vendor/theme-<theme>`
- `autoload.psr-4`: match the new namespace
- `extra.laravel.providers`: new provider class

### 5) Update Vite config + asset paths

Edit `themes/<vendor>/<theme>/vite.config.js`:

- `buildDirectory`: `themes/<vendor>/<theme>/build`
- `outDir`: `public/themes/<vendor>/<theme>/build`
- `hotFile`: `public/themes/<vendor>/<theme>/hot`

### 6) Update Blade layout asset paths

Edit theme layouts to point at the new build directory:

`@vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/<vendor>/<theme>/build')`

Update in:

- `themes/<vendor>/<theme>/views/layouts/default.blade.php`
- `themes/<vendor>/<theme>/views/layouts/landing.blade.php`
- `themes/<vendor>/<theme>/views/layouts/post.blade.php`
- `themes/<vendor>/<theme>/views/posts/index.blade.php`

### 7) Sync and activate

```bash
php artisan tp:themes sync
php artisan tp:themes activate <vendor>/<theme>
```

### 8) Install/build theme assets

```bash
bun install --cwd themes/<vendor>/<theme>
bun run --cwd themes/<vendor>/<theme> dev
```

## Block Data Shape (What Layouts Receive)

Rendered layouts receive pre-rendered block HTML (via `$blocksHtml`) and blocks originate from a normalized JSON
structure. The normalized block shape is:

```json
{
  "type": "blocks/hero",
  "version": 3,
  "props": {
    "headline": "Your headline"
  },
  "variant": "default"
}
```

Rules:

- `type` (string): Required. Registry key like `blocks/hero`.
- `version` (int): Resolved from block definition if missing.
- `props` (object): Field payload for the block. Defaults are shallow-merged.
- `variant` (string, optional): Only if the block defines variants.

UI-only editor keys exist during editing but are stripped on save:

- `_key` (string): Editor-only unique ID.
- `_collapsed` (bool): Editor-only UI state.

Block fields are defined per block in
`plugins/tentapress/blocks/resources/definitions/*.json` and should be treated as the source of truth for `props`.
