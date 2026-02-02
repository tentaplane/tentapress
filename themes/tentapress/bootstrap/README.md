# Bootstrap Theme

A Bootstrap 5 theme for TentaPress.

## Theme Details

| Field | Value |
|-------|-------|
| ID | `tentapress/bootstrap` |
| Version | 0.1.3 |
| CSS Framework | Bootstrap 5 |

## Layouts

| Key | Label | Description |
|-----|-------|-------------|
| default | Default | Standard page layout |
| landing | Landing | Full-width landing page |

## Menu Locations

| Key | Label |
|-----|-------|
| primary | Primary Navigation |
| footer | Footer Navigation |

## Assets

This theme uses Bootstrap 5 CSS via Vite.

### Build

```bash
# Install dependencies
bun install --cwd themes/tentapress/bootstrap

# Build assets
bun run --cwd themes/tentapress/bootstrap build

# Development with HMR
bun run --cwd themes/tentapress/bootstrap dev
```

### Entrypoints

- `resources/css/theme.css` - Bootstrap + custom styles
- `resources/js/theme.js` - Bootstrap JS + custom scripts

## Structure

```
bootstrap/
├── composer.json       # Composer metadata
├── package.json        # NPM dependencies
├── vite.config.js      # Vite configuration
├── tentapress.json     # Theme manifest
├── screenshot.webp     # Theme preview
├── src/
│   └── BootstrapThemeServiceProvider.php
├── views/
│   └── layouts/
│       ├── default.blade.php
│       └── landing.blade.php
└── resources/
    ├── css/theme.css
    └── js/theme.js
```

## Customization

Override block views by creating `views/blocks/{block-key}.blade.php`.
