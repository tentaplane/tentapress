# Themes

- Audience: theme authors/agents working in `themes/*/*`.
- Goal: consistent, minimal themes with predictable layouts/blocks and clear metadata.

## Standard layout (per theme)

- `tentapress.json` (manifest; see rules below).
- `package.json` (theme-local build scripts).
- `views/`
    - `layouts/` (e.g., `default.blade.php`, `landing.blade.php`).
    - `blocks/` (theme-specific overrides for block partials).
- `resources/` (theme CSS/JS entrypoints).
- `public/themes/<vendor>/<theme>/build/` (compiled assets + `manifest.json`).
- `screenshot.webp` (preview).

## Manifest rules (`tentapress.json`)

- Required fields: `type` = `theme`, `id` (`vendor/name`), `name`, `version`, `description`, `layouts` (array of
  `{ key, label }`).
- Keep ids unique; avoid duplicate manifests. Aim for future JSON Schema validation.

## Layouts and blocks

- Provide at least one layout; match `layouts` keys to Blade files under `views/layouts`.
- Blocks: reuse shared block partials where possible; place overrides in `views/blocks` named by block key.
- Keep layouts small: include shared partials, yield slots/sections for content, avoid inline JS.

## Assets

### Per-theme build (required)

- Each theme owns its Vite config + entrypoints under `themes/<vendor>/<theme>/resources/`.
- Theme `package.json` must include its own dev dependencies (e.g. `vite` plus `tailwindcss`/`@tailwindcss/vite` or
  `bootstrap`).
- Build output lives at `public/themes/<vendor>/<theme>/build/manifest.json` and `assets/…`.
- Theme layouts should call
  `@vite(['resources/css/theme.css', 'resources/js/theme.js'], 'themes/<vendor>/<theme>/build')`.
- Build commands:
    - `bun install --cwd themes/<vendor>/<theme>`
    - `bun run --cwd themes/<vendor>/<theme> build`

### Styling rules

- Tailwind v4 via CSS `@import "tailwindcss"`; ensure class sources are covered in the theme CSS.
- Bootstrap v5 via CSS `@import "bootstrap/dist/css/bootstrap.css"` and (optional) JS import in the theme entrypoint.
- Keep theme CSS/JS scoped; avoid leaking global resets.
- Prefer utilities/variables already in admin kit when relevant; keep custom styles minimal.

## Validation and testing (lightweight)

- Check manifest completeness and that each layout key has a matching Blade file.
- Keep screenshots up to date with layouts.
- (Future) Add a small test to assert manifest validity and layout discovery once a test runner is configured.

## Checklist: new theme

- [ ] Create `tentapress.json` with type/id/name/version/description/layouts.
- [ ] Add `package.json` and `vite.config.js` to compile theme assets.
- [ ] Ensure theme `package.json` includes `vite` and either `tailwindcss`/`@tailwindcss/vite` or `bootstrap`.
- [ ] Add `views/layouts` and ensure keys match manifest.
- [ ] Add `views/blocks` only for overrides; otherwise rely on shared partials.
- [ ] Add `screenshot.webp`.
- [ ] Add `resources/css/theme.css` and `resources/js/theme.js`.
