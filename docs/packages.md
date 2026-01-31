# Platform (packages/tentapress/system)

- Audience: contributors touching the platform layer (registries, middleware, console, manifests).
- Goal: keep platform small, deterministic, and friendly to plugins/themes.

## Responsibilities

- Discover and register plugins/themes from the filesystem.
- Maintain manifest cache and DB state (`tp_plugins`, theme cache) with clear errors.
- Provide admin middleware, routing helpers, and console commands.
- Define capability names and shared contracts/hooks.

## Registries and manifests

- PluginRegistry/ThemeRegistry: scan filesystem for `tentapress.json`, parse manifests, upsert DB/cache.
- Protected plugin ids: core plugins should not be disabled without `--force`.
- Defaults: `config/tentapress.php` lists default plugins; enabled on first sync and via `tp:plugins defaults`.
- Missing providers: cache build skips missing providers and auto-disables them in `tp_plugins`.
- Duplicates: last one wins today—prefer failing fast with validation.
- Recommendation: add JSON Schema validation for manifests (id/name/version/provider/layouts/admin menus) and surface actionable errors when malformed.

## Middleware and routing

- Admin middleware group handles auth, capabilities, and error pages; register aliases centrally.
- Admin routes should be named `tp.*` and live under the admin group; public routes remain separate.
- Keep middleware thin; avoid business logic inside middleware.

## Console commands

- Plugins sync/enable/defaults: keep output concise; fail clearly when manifests missing/invalid.
- Themes command: list/activate themes based on registry data.
- Re-run migrations after enabling plugins that add tables.

## Capabilities and constants

- Define capability strings in one place (enum/constants) to avoid stringly-typed checks across plugins.
- Share capability map with menu builder and middleware for consistent checks.

## Extensibility hooks

- Use container tags for adapters (e.g., deployment adapters, block kits) and resolve via registries.
- Document required methods/shape for each tag to keep adapters consistent.

## Error handling and logging

- Use `throw_if/abort_if` with actionable messages for guard clauses (missing plugin, malformed manifest).
- Log operational failures with context: plugin/theme id, manifest path, adapter id.

## Testing (add when test runner exists)

- Smoke tests: manifest discovery, duplicate handling, protected plugin behavior, cache write/read, defaults enablement.
- Command tests: plugins sync/enable/defaults, themes listing/activation.

## Suggested roadmap

- Add manifest JSON Schema + validator; fail sync on invalid manifests and list offending paths.
- Centralize capabilities constants and require their use in menus/routes/middleware checks.
- Add container tags for adapters/blocks and document expectations.
- Add smoke tests for registries/commands once phpunit/pest config is in place.
