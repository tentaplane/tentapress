# Tailwind Theme

A Tailwind CSS v4 theme for TentaPress.

## Theme Details

| Field         | Value                 |
|---------------|-----------------------|
| ID            | `tentapress/tailwind` |
| Version       | 0.2.0                 |
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

# Build assets
bun run --cwd themes/tentapress/tailwind build
```

Note: this theme currently supports build-only workflows (no HMR/dev server).

### Entrypoints

- `resources/css/theme.css` - Tailwind import + custom styles
- `resources/js/theme.js` - Alpine.js + custom scripts

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
