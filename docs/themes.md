# Themes

- Audience: theme authors/agents working in `themes/*/*`.
- Goal: consistent themes with predictable layouts/blocks, clear metadata, and a documented starter workflow.
- Default starter: `themes/tentapress/tailwind`.

## Recommended starting point

Use `themes/tentapress/tailwind` as the base for new themes.
It reflects the current contract for manifests, Vite asset paths, theme-local hot file support, menu locations, and
builder preview behavior.

For a new theme, prefer cloning the starter instead of assembling files by hand.

## Standard layout (per theme)

- `composer.json`
- `tentapress.json`
- `package.json`
- `vite.config.js`
- `src/`
    - Theme service provider, usually `<ThemeName>ThemeServiceProvider.php`
- `views/`
    - `layouts/` such as `default.blade.php`, `landing.blade.php`, `post.blade.php`
    - `components/` such as `header.blade.php`, `menu.blade.php`, `footer.blade.php`
    - `blocks/` for theme-specific block overrides
    - `posts/index.blade.php` for post listings when the theme supplies one
- `resources/`
    - `css/theme.css`
    - `js/theme.js`
- `bin/`
    - `build.sh`
    - `watch.sh`
    - optional starter tooling such as `clone.sh`
- `screenshot.jpg`, `screenshot.jpeg`, `screenshot.png`, or `screenshot.webp`
- Build output: `public/themes/<vendor>/<theme>/build/`

## Manifest rules (`tentapress.json`)

Required fields:

- `type`: must be `theme`
- `id`: `vendor/name`
- `name`
- `version`
- `description`
- `layouts`: array of `{ key, label }`

Common additional fields in current tooling:

- `provider`: fully-qualified Laravel service provider class
- `provider_path`: provider file path relative to the theme root
- `menu_locations`: object of `{ key: label }` pairs for navigation slots

Example:

```json
{
  "type": "theme",
  "id": "tentapress/tailwind",
  "name": "Tailwind by TentaPress",
  "version": "0.4.2",
  "description": "A Tailwind v4 theme for TentaPress.",
  "provider": "TentaPress\\Themes\\Tailwind\\TailwindThemeServiceProvider",
  "provider_path": "src/TailwindThemeServiceProvider.php",
  "layouts": [
    { "key": "default", "label": "Default" },
    { "key": "landing", "label": "Landing" },
    { "key": "post", "label": "Post" }
  ],
  "menu_locations": {
    "primary": "Primary Navigation",
    "footer": "Footer Navigation"
  }
}
```

Rules:

- Keep `id` unique across all themes.
- Layout keys must match Blade files under `views/layouts`.
- If `provider` is set, `provider_path` should point at the matching file.
- Keep manifest data aligned with `composer.json`, namespaces, and Vite build paths.

## Layouts and blocks

- Provide at least one layout.
- Match `layouts[].key` to `views/layouts/<key>.blade.php`.
- Standard layouts in current themes are:
    - `default`
    - `landing`
    - `post`
- Themes typically also provide `views/posts/index.blade.php` for post archive pages.
- Reuse shared block rendering where possible; override only the blocks you need in `views/blocks`.
- Variant-specific overrides follow the existing convention, for example `views/blocks/hero/default.blade.php`.
- Keep layouts small: compose headers/footers/components, render `{!! $blocksHtml !!}`, avoid large inline scripts.

## Assets

### Current Vite contract

Each theme owns its own Vite config and entrypoints.

Expected entrypoints:

- `resources/css/theme.css`
- `resources/js/theme.js`

Expected Blade usage:

```blade
@vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/<vendor>/<theme>/build')
```

Expected `vite.config.js` behavior:

- `input`: `resources/css/theme.css`, `resources/js/theme.js`
- `buildDirectory`: `themes/<vendor>/<theme>/build`
- `outDir`: `public/themes/<vendor>/<theme>/build`
- `hotFile`: `public/themes/<vendor>/<theme>/hot`

The Tailwind starter also uses a theme service provider to switch Laravel Vite to the theme-local hot file during dev.

### Supported commands

From the repo root:

```bash
bun install --cwd themes/<vendor>/<theme>
bun run --cwd themes/<vendor>/<theme> dev
bun run --cwd themes/<vendor>/<theme> watch
bun run --cwd themes/<vendor>/<theme> build
```

Theme-local helper scripts are also available when present:

```bash
themes/<vendor>/<theme>/bin/build.sh
themes/<vendor>/<theme>/bin/watch.sh
```

Notes:

- `dev` uses Vite dev server / HMR.
- `watch` produces watched builds without HMR.
- `build` writes production assets and `manifest.json` into `public/themes/<vendor>/<theme>/build`.

### Styling rules

- Tailwind v4 uses CSS-first configuration with `@import "tailwindcss"`.
- Ensure `@source` entries in the theme CSS cover the theme Blade and JS files.
- Keep CSS/JS scoped to the theme.
- Avoid assuming admin CSS utilities are available on the public theme.

## Service provider

If the theme declares a provider, keep it focused on theme runtime concerns.

Current starter behavior:

- Watches for `public/themes/<vendor>/<theme>/hot`
- Calls `Vite::useHotFile(...)` when the hot file exists

That pattern allows theme-local HMR without changing the app-wide Vite configuration.

## Distribution and installation

- Themes are discovered from `themes/**/tentapress.json`.
- Theme discovery scans `themes/`, not `vendor/`.
- After adding or changing manifests, run:

```bash
php artisan tp:themes sync
```

- To activate a theme:

```bash
php artisan tp:themes activate <vendor>/<theme>
```

Useful related commands:

```bash
php artisan tp:themes list
php artisan tp:themes cache
```

## Builder preview contract

`tentapress/builder` renders previews using the active theme's normal layout views.
No preview-only layout directory is required.

Theme layouts should therefore be safe to render in both:

- public frontend requests
- builder preview rendering

## Cloning the default starter

Prefer the provided cloning helper:

```bash
themes/tentapress/tailwind/bin/clone.sh --vendor <vendor> --theme <theme> [--name "Display Name"]
```

Example:

```bash
themes/tentapress/tailwind/bin/clone.sh --vendor ministry --theme service-manual --name "Service Manual Theme"
```

What the helper updates automatically:

- destination folder under `themes/<vendor>/<theme>`
- `tentapress.json` `id`, `name`, `version`, `provider`, `provider_path`
- `composer.json` package name, PSR-4 namespace, Laravel provider registration
- provider class filename and namespace
- `vite.config.js` theme build/hot paths
- Blade `@vite(...)` build directory references
- `README.md` starter identifiers

The helper expects lowercase `vendor` and `theme` folder names.

## Manual clone checklist

If you are not using `bin/clone.sh`, update all of the following:

- `tentapress.json`
    - `id`
    - `name`
    - `version`
    - `provider`
    - `provider_path`
    - `layouts`
    - `menu_locations`
- `composer.json`
    - `name`
    - `autoload.psr-4`
    - `extra.laravel.providers`
- `src/*`
    - namespace
    - provider class name
    - hot file path if hardcoded
- `vite.config.js`
    - `buildDirectory`
    - `outDir`
    - `hotFile`
- Blade templates
    - `@vite(..., 'themes/<vendor>/<theme>/build')`
- `README.md`
- screenshot asset

## Validation checklist

- `tentapress.json` exists and `type` is `theme`
- each manifest layout key has a matching file in `views/layouts`
- provider class exists if declared
- Vite output paths point to `public/themes/<vendor>/<theme>/build`
- layouts reference the same build directory in `@vite(...)`
- screenshot file exists
- theme builds successfully
- `php artisan tp:themes sync` discovers the theme

## Checklist: new theme

- [ ] Start from `themes/tentapress/tailwind`
- [ ] Clone with `bin/clone.sh` or copy the folder manually
- [ ] Update manifest metadata and namespaces
- [ ] Keep `views/layouts` aligned with manifest layout keys
- [ ] Add `views/blocks` only for overrides
- [ ] Add or update screenshot image
- [ ] Ensure `resources/css/theme.css` and `resources/js/theme.js` exist
- [ ] Confirm `vite.config.js` uses theme-local build and hot file paths
- [ ] Run `bun install --cwd themes/<vendor>/<theme>`
- [ ] Run `bun run --cwd themes/<vendor>/<theme> build`
- [ ] Run `php artisan tp:themes sync`

## Block data shape (what layouts receive)

Rendered layouts receive pre-rendered block HTML via `$blocksHtml`.
Blocks originate from a normalized JSON structure like:

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

- `type` (string): required block registry key, for example `blocks/hero`
- `version` (int): resolved from block definition if missing
- `props` (object): field payload, shallow-merged with defaults
- `variant` (string, optional): used when the block defines variants

Editor-only keys may appear during editing but are stripped before save:

- `_key` (string)
- `_collapsed` (bool)

Block field definitions live in `plugins/tentapress/blocks/resources/definitions/*.json` and are the source of truth
for block `props`.
