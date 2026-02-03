# Open-source distribution

## Goal
Define how TentaPress core + plugins/themes are distributed as open source, including versioning and release workflow.

## Current state
- Monorepo with Composer path repos under `plugins/*/*` and `packages/*/*`.
- Themes live under `themes/*/*` and are copied into `themes/` for local editing.
- Split-publish workflow exists: `.github/workflows/split-publish.yml`.
- Split workflow pushes each plugin/theme to its own GitHub repository and tags/releases it when the version changes.

## Strategy (current)

### Monorepo + split repos (recommended and implemented)
- Keep all code in the monorepo for coordinated development.
- Publish each plugin and theme as its own GitHub repository via split workflow.
- Register split repositories on Packagist for Composer installs.

## Split workflow details

- Workflow file: `.github/workflows/split-publish.yml`.
- Trigger: push to `main` (plugins/themes) or manual dispatch.
- Naming:
  - Plugins → `plugin-<folder-name>`
  - Themes → `theme-<folder-name>`
- Required secret:
  - `SPLIT_TOKEN` (PAT with repo write access, SSO-authorized if needed).
- Optional config:
  - `SPLIT_ORG` secret to override org/user.
  - `SPLIT_PLUGIN_PREFIX` / `SPLIT_THEME_PREFIX` to override naming.
- Tagging/releases:
  - Tags use `v<version>` from each `tentapress.json`.
  - Tags/releases are created for both plugins and themes when the version changes.

## Release workflow (current)
1. Bump version in `tentapress.json` (and README if referenced).
2. Ensure `composer.json` exists for plugins/themes that should be Packagist-ready.
3. Merge to `main` and let split-publish push + tag split repos.
4. Packagist picks up tags/releases from the split repos.

## Versioning
- Use semver per package.
- Keep a compatibility matrix (core + plugin versions that align) once dependencies stabilize.

## Distribution artifacts
- Provide a “starter” release with core + default plugins enabled.
- Document how to add optional plugins via Composer + `tp:plugins enable`.
