# Project Status and Roadmap

**Last updated:** 2026-02-03

Single source of truth for current status, priorities, and near-term roadmap.

## Summary
TentaPress core and first-party plugins are in place. Current focus is a modern blocks editor experience and a
showcase Tailwind base theme. Distribution is via split repos and Packagist-ready Composer packages.

## Decisions (Implemented)
- Plugin and theme discovery is filesystem-based via `tentapress.json`.
- Admin assets live in `admin-shell` and are compiled by the admin-shell Vite build.
- Themes own their own build toolchain and are compiled per theme.
- Theme installs are copied from `vendor/` into `themes/<vendor>/<theme>` for local edits.
- Split-publish workflow pushes plugins/themes to separate GitHub repos and tags/releases on version changes.

## Current Priority (P0)
### 1) Blocks editor overhaul (Notion/Gutenberg-style)
- Goal: modern, fast, full-screen authoring across Pages/Posts.
- Scope: shared editor shell, block list, drag/drop, inline editing, block settings, quick insert.
- Recent progress:
  - Full-screen editor + shared editor surface for Pages/Posts.
  - Cleaner block list with clearer summaries and insert controls.
  - Block library palette and drag/insert flow.
- Next:
  - Tighten editing ergonomics (focus, keyboard shortcuts, quick insert).
  - Stabilize drag/insert behavior and reduce micro-movements.
  - Improve block palette discoverability and filtering.

## Secondary Priorities (P1–P2)
### 2) Tailwind base theme showcase
- Goal: bold, production-grade reference theme that demonstrates blocks and layouts.
- Status: in progress; default layout and block overrides landed, demo page seeded.
- Next: polish landing/post layouts, tighten typography/spacing scale, review block coverage.

### 3) SEO analytics tags
- Add settings for head/footer JS tags with safe rendering.

### 4) Plugin generator
- `tp:plugin make` scaffold with manifest, provider, routes, views, assets.

### 5) CLI documentation
- Single canonical reference for user-facing commands and examples.

### 6) Forms builder (exploratory)
- MVP: form block + submissions table + email notifications.

## Quality and docs gaps
- No test harness in CI yet; add PHPUnit/Pest when ready.
- ADRs/PRDs not yet written for key decisions/features.

## Release/distribution notes
- Bump `tentapress.json` version for any plugin/theme release.
- Ensure `composer.json` exists for Packagist distribution.
- Split-publish workflow pushes + tags split repos; Packagist ingests tags.
