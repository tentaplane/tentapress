# Admin assets

## Goal
Keep admin CSS/JS owned by the `admin-shell` plugin while allowing the root Vite build to compile them for the app.

## Current state
- Admin assets live in the admin-shell plugin:
  - `plugins/tentapress/admin-shell/resources/css/admin.css`
  - `plugins/tentapress/admin-shell/resources/js/admin.js`
- Root `vite.config.js` compiles admin assets + a public fallback stylesheet.
- Themes compile their own assets within theme folders.
- Admin layouts reference plugin assets via `@vite([...])`.

## Notes
- Admin assets are plugin-owned but built by the root Vite config.
- Theme assets are intentionally excluded from the root build.
